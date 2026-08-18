<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\ProductVariant;
use App\Models\ImageModel;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = 10;
        $categories = Categories::where('status', 1)->get();
        $supplier = Supplier::where('status', 1)->get();
        $products = Product::with(['supplier', 'category', 'productImage'])
            ->withSum('variants', 'quantity')->paginate($perPage);

        return view("product.index", compact("products", "categories", "supplier"));
    }

    public function getData()
    {
        $perPage = 10;
        $products = Product::with(['supplier', 'category', 'productImage'])
            ->withSum('variants', 'quantity')->paginate($perPage);

        return view("product.data", compact("products"));
    }

    public function search(Request $request)
    {
        $keyword = trim($request->input('keyword')); // Lấy từ khóa từ request và loại bỏ khoảng trắng thừa

        $query = Product::with(['supplier', 'category', 'productImage'])
            ->withSum('variants', 'quantity');

        // Không cache kết quả tìm kiếm: dữ liệu sản phẩm thay đổi liên tục,
        // cache theo từ khóa sẽ trả về tồn kho/giá đã cũ.
        if (!empty($keyword)) {
            $products = $query->where('product_name', 'ilike', "%{$keyword}%")->get();
        } else {
            $perPage = 15;
            $products = $query->paginate($perPage);
        }

        return view("product.data", compact("products"));
    }

    public function getProductById(string $id)
    {
        $products = Product::with(['supplier', 'category', 'productImage', 'variants'])
            ->withSum('variants', 'quantity')
            ->where('products.id', $id)
            ->get();

        return $products;
    }

    public function getImageUrl(string $id)
    {
        $media = ImageModel::where('product_id', $id)->orderBy('sort_order')->get();

        return response()->json([
            'imageUrl' => $media,
            'paths' => $media->map(fn($m) => Storage::url($m->path))->all(),
            // Để JS biết render <img> hay <video> cho từng phần tử.
            'types' => $media->pluck('media_type')->all(),
        ]);
    }

    public function deleteImageUrl(string $id)
    {
        $image = ImageModel::find($id);
        if (!$image) {
            return response()->json(['error' => 'Không tìm thấy hình ảnh!'], 404);
        }

        Storage::delete($image->path);
        $image->delete();
        return response()->json(['success' => 'Ảnh sản phẩm đã được xóa thành công!']);
    }



    public function generateBarcode($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không tồn tại.'], 404);
        }

        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($product->barcode, $generator::TYPE_CODE_128);

        // Trả về hình ảnh mã vạch dưới dạng response
        return response($barcode)->header('Content-Type', 'image/png');
    }

    public function generateQrCode($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không tồn tại.'], 404);
        }

        $qrContent = $product->barcode;

        $qrCode = new QrCode($qrContent);
        $qrCode->setEncoding(new Encoding('UTF-8'));
        $qrCode->setSize(300);
        $qrCode->setMargin(10);

        $writer = new PngWriter();

        // Đối với việc trả về hình ảnh trực tiếp
        $response = response($writer->write($qrCode)->getString(), 200, ['Content-Type' => 'image/png']);

        return $response;
    }


    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     if ($request->isMethod('POST') || $request->ajax() || $request->wantsJson()) {
    //         $request->validate([
    //             'product_name' => 'required|max:255',
    //             'sell_price' => 'required|integer|min:0',
    //             'import_price' => 'required|integer|min:0',
    //             'total_quantity' => 'required|integer|min:0',
    //             'unit' => 'required|max:30',
    //             'supplier_id' => 'required',
    //             'categories_id' => 'required',
    //             'images.*' => 'file|image|max:2048', // Mỗi file phải là hình ảnh và không quá 2MB
    //             'pin_image' => 'nullable',
    //         ]);

    //         $params = $request->except('_token');

    //         $params['barcode'] = Str::uuid()->toString();
    //         $params['status'] = 1;
    //         $product = Product::create($params);

    //         if ($product->id && $request->hasFile('images')) {
    //             $images = $request->file('images');
    //             foreach ($images as $image) {
    //                 // Lưu trữ file vào thư mục và lấy đường dẫn
    //                 $path = $image->store('public/images');

    //                 ImageModel::create([
    //                     'product_id' => $product->id,
    //                     'path' => $path,
    //                     'name' => $image->getClientOriginalName(),
    //                 ]);
    //             }
    //         }

    //         // Cập nhật total_import_price cho supplier
    //         $supplier = Supplier::find($request->supplier_id);
    //         if ($supplier) {
    //             $supplier->total_import_price += $request->import_price * $request->total_quantity;
    //             $supplier->save();
    //         }

    //         return response()->json(['success' => 'Sản phẩm đã được thêm thành công!',]);
    //     }
    // }


    /**
     * Display the specified resource.
     */
    public function store(Request $request)
    {
        $data = $request->validate($this->productRules());

        DB::beginTransaction();
        try {
            $data['barcode'] = Str::uuid()->toString();
            $data['slug'] = $this->uniqueSlug($data['product_name']);
            $data['is_featured'] = $request->boolean('is_featured');
            // Tồn kho nằm ở biến thể; trạng thái được đồng bộ lại sau khi lưu biến thể.
            $data['status'] = 2;

            $product = Product::create($data);

            $this->storeMedia($product, $request);
            $totalQuantity = $this->syncVariants($product, $request->input('variants', []));
            $this->syncProductStatus($product, $totalQuantity);

            DB::commit();
            return response()->json(['success' => 'Sản phẩm đã được thêm thành công!']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Thêm sản phẩm thất bại: ' . $e->getMessage());
            return response()->json(['error' => 'Không thêm được sản phẩm: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(Request $request, string $id)
    // {
    //     $product = Product::find($id);
    //     if (!$product) {
    //         return response()->json(['error' => 'Sản phẩm không tồn tại.'], 404);
    //     }

    //     $request->validate([
    //         'product_name' => 'required|max:255',
    //         'sell_price' => 'required|integer|min:0',
    //         'import_price' => 'required|integer|min:0',
    //         'total_quantity' => 'required|integer|min:0',
    //         'unit' => 'required|max:30',
    //         'supplier_id' => 'required',
    //         'categories_id' => 'required',
    //         'status' => 'required',
    //         'images.*' => 'file|image|max:2048',
    //         'pin_image' => 'nullable',
    //     ]);

    //     $params = $request->except('_token');
    //     $params['barcode'] = $product->barcode;

    //     $result = $product->update($params);

    //     if ($request->hasFile('images')) {
    //         // Xóa hình ảnh cũ
    //         $oldImages = ImageModel::where('product_id', $id)->get();
    //         foreach ($oldImages as $oldImage) {
    //             Storage::delete($oldImage->path);
    //             $oldImage->delete();
    //         }
    //         // Lưu hình ảnh mới
    //         $images = $request->file('images');
    //         foreach ($images as $image) {
    //             $path = $image->store('public/images');
    //             ImageModel::create([
    //                 'product_id' => $product->id,
    //                 'path' => $path,
    //                 'name' => $image->getClientOriginalName(),
    //             ]);
    //         }
    //     }

    //     if ($result) {
    //         return redirect()->route('product')->with('success', 'Sản phẩm đã được cập nhật thành công!');
    //     } else {
    //         return back()->withInput()->with('error', 'Cập nhật sản phẩm thất bại.');
    //     }
    // }

    public function edit(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không tồn tại.'], 404);
        }

        $data = $request->validate($this->productRules($product));

        DB::beginTransaction();
        try {
            if ($product->product_name !== $data['product_name']) {
                $data['slug'] = $this->uniqueSlug($data['product_name'], $product->id);
            }
            $data['is_featured'] = $request->boolean('is_featured');

            $product->update($data);

            // Phải lưu media trước: ảnh đại diện có thể chính là ảnh vừa upload,
            // đổi ghim trước khi lưu thì không tìm thấy tên file nên bị bỏ qua.
            $this->storeMedia($product, $request);
            $this->syncPinnedMedia($product, $request->input('pin_image'));
            $totalQuantity = $this->syncVariants($product, $request->input('variants', []));
            $this->syncProductStatus($product, $totalQuantity);

            DB::commit();
            // Trả JSON vì form sửa gửi bằng AJAX; bản cũ redirect()->back() khiến JS không đọc được kết quả.
            return response()->json(['success' => 'Sản phẩm đã được cập nhật thành công!']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Sửa sản phẩm thất bại: ' . $e->getMessage());
            return response()->json(['error' => 'Không sửa được sản phẩm: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Quy tắc hợp lệ dùng chung cho thêm và sửa sản phẩm.
     * Cho phép tải lên cả ảnh lẫn video trong cùng một trường `media`.
     */
    private function productRules(?Product $product = null): array
    {
        return [
            'product_name' => 'required|max:255',
            'categories_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string|max:5000',
            'material' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            // Khớp đúng 4 giá trị bộ lọc "đối tượng" của web bán hàng.
            'audience' => 'nullable|in:Nam,Nữ,Trẻ em,Unisex',
            'unit' => 'nullable|max:30',
            'import_price' => 'required|integer|min:0',
            'sell_price' => 'required|integer|min:0',
            'discount_price' => 'nullable|integer|min:0|lte:sell_price',
            'is_featured' => 'nullable|boolean',

            // Media: ảnh tối đa 5MB, video tối đa 50MB (giới hạn theo từng file ở dưới).
            'media' => 'nullable|array',
            'media.*' => [
                'file',
                'max:51200',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime,video/webm',
            ],
            'pin_image' => 'nullable|string',

            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer|exists:product_variants,id',
            'variants.*.size' => 'nullable|string|max:50',
            'variants.*.color' => 'nullable|string|max:50',
            'variants.*.quantity' => 'nullable|integer|min:0',
            'variants.*.price_override' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Lưu media mới (ảnh hoặc video) và nối vào cuối danh sách hiện có.
     *
     * DB đặt ở xa (~300ms mỗi round-trip) nên phần này cố ý gộp truy vấn:
     * 1 truy vấn thống kê + 1 lệnh chèn hàng loạt, thay vì 2 + N như trước.
     */
    private function storeMedia(Product $product, Request $request): void
    {
        if (!$request->hasFile('media')) {
            return;
        }

        // Gộp "sort_order lớn nhất" và "đã có ảnh ghim chưa" vào cùng một truy vấn.
        $stats = ImageModel::where('product_id', $product->id)
            ->selectRaw('COALESCE(MAX(sort_order), 0) AS max_sort')
            ->selectRaw('COALESCE(SUM(CASE WHEN is_pined THEN 1 ELSE 0 END), 0) AS pinned_count')
            ->first();

        $sortOrder = (int) ($stats->max_sort ?? 0);
        $hasPinned = (int) ($stats->pinned_count ?? 0) > 0;
        $now = now();
        $rows = [];

        foreach ($request->file('media') as $file) {
            $isVideo = str_starts_with((string) $file->getMimeType(), 'video/');
            $name = $file->getClientOriginalName();
            $shouldPin = !$isVideo && !$hasPinned && $request->pin_image === $name;

            $rows[] = [
                'product_id' => $product->id,
                'path' => $file->store('public/images'),
                'name' => $name,
                'media_type' => $isVideo ? ImageModel::TYPE_VIDEO : ImageModel::TYPE_IMAGE,
                'sort_order' => ++$sortOrder,
                // Video không dùng làm ảnh đại diện được, nên chỉ ảnh mới được ghim.
                'is_pined' => $shouldPin,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Tránh ghim hai media khi người dùng gửi lên nhiều file trùng tên.
            $hasPinned = $hasPinned || $shouldPin;
        }

        if ($rows) {
            ImageModel::insert($rows);
        }
    }

    /**
     * Đổi ảnh đại diện sang một media đã có sẵn (theo tên file).
     */
    private function syncPinnedMedia(Product $product, ?string $pinName): void
    {
        if (empty($pinName)) {
            return;
        }

        $pinned = ImageModel::where('product_id', $product->id)
            ->where('name', $pinName)
            ->where('media_type', ImageModel::TYPE_IMAGE)
            ->first();

        // Đã ghim đúng ảnh đó rồi thì thôi, khỏi tốn thêm 2 truy vấn mỗi lần sửa.
        if (!$pinned || $pinned->is_pined) {
            return;
        }

        ImageModel::where('product_id', $product->id)->update(['is_pined' => false]);
        $pinned->is_pined = true;
        $pinned->save();
    }

    /**
     * Lưu ma trận biến thể size/màu gửi từ form và trả về tổng tồn kho.
     * Biến thể không còn trong danh sách gửi lên sẽ bị xóa.
     *
     * Nạp một lần rồi so khớp trong PHP: bản cũ gọi firstOrNew + save cho từng
     * dòng (2 round-trip mỗi biến thể), 12 biến thể là đã mất ~8 giây chờ DB.
     */
    private function syncVariants(Product $product, array $variants): int
    {
        $existing = ProductVariant::where('product_id', $product->id)
            ->get()
            ->keyBy(fn($v) => $this->variantKey($v->size, $v->color));

        $keptIds = [];
        $inserts = [];
        $seen = [];
        $sortOrder = 0;
        $total = 0;
        $now = now();

        foreach ($variants as $row) {
            $size = trim((string) ($row['size'] ?? '')) ?: null;
            $color = trim((string) ($row['color'] ?? '')) ?: null;

            // Dòng trống hoàn toàn thì bỏ qua, tránh tạo biến thể rác.
            if ($size === null && $color === null) {
                continue;
            }

            $key = $this->variantKey($size, $color);

            // Form có thể gửi trùng tổ hợp size/màu; giữ dòng đầu tiên để không
            // vi phạm ràng buộc unique(product_id, size, color).
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $quantity = max(0, (int) ($row['quantity'] ?? 0));
            $rawPrice = $row['price_override'] ?? null;
            $priceOverride = ($rawPrice === null || $rawPrice === '') ? null : (int) $rawPrice;
            $order = $sortOrder++;
            $total += $quantity;

            $variant = $existing->get($key);

            if (!$variant) {
                $inserts[] = [
                    'product_id' => $product->id,
                    'size' => $size,
                    'color' => $color,
                    'sku' => $product->barcode . '-' . strtoupper(Str::random(5)),
                    'quantity' => $quantity,
                    'price_override' => $priceOverride,
                    'sort_order' => $order,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                continue;
            }

            $keptIds[] = $variant->id;

            // Chỉ ghi khi giá trị thật sự đổi: mỗi UPDATE thừa là một round-trip thừa.
            if (
                $variant->quantity !== $quantity
                || $variant->price_override !== $priceOverride
                || $variant->sort_order !== $order
            ) {
                $variant->quantity = $quantity;
                $variant->price_override = $priceOverride;
                $variant->sort_order = $order;
                $variant->save();
            }
        }

        // Xóa trước rồi mới chèn: biến thể mới chưa có id nên không nằm trong
        // $keptIds, nếu xóa sau sẽ quét mất chính những dòng vừa thêm.
        ProductVariant::where('product_id', $product->id)
            ->whereNotIn('id', $keptIds ?: [0])
            ->delete();

        if ($inserts) {
            ProductVariant::insert($inserts);
        }

        return $total;
    }

    /**
     * Khóa so khớp biến thể: phân biệt "không có" với chuỗi rỗng.
     */
    private function variantKey(?string $size, ?string $color): string
    {
        return ($size ?? '~') . '||' . ($color ?? '~');
    }

    /**
     * Đồng bộ trạng thái còn/hết hàng từ tổng tồn kho đã tính sẵn ở syncVariants,
     * tránh chạy thêm một truy vấn SUM nữa.
     */
    private function syncProductStatus(Product $product, int $totalQuantity): void
    {
        $status = $totalQuantity > 0 ? 1 : 2;

        if ((int) $product->status === $status) {
            return;
        }

        $product->status = $status;
        $product->save();
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'san-pham';

        // Lấy toàn bộ slug cùng tiền tố trong một truy vấn, thay vì lặp exists()
        // cho từng hậu tố (mỗi vòng lặp là một round-trip tới DB ở xa).
        $taken = Product::withTrashed()
            ->where(fn($q) => $q->where('slug', $base)->orWhere('slug', 'like', $base . '-%'))
            ->when($ignoreId, fn($q) => $q->whereKeyNot($ignoreId))
            ->pluck('slug')
            ->all();

        if (!in_array($base, $taken, true)) {
            return $base;
        }

        $suffix = 2;
        while (in_array($base . '-' . $suffix, $taken, true)) {
            $suffix++;
        }

        return $base . '-' . $suffix;
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Xác định sản phẩm cần xóa
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không tồn tại.'], 404);
        }

        // Xóa tất cả các ảnh có product_id bằng với id của sản phẩm
        $images = ImageModel::where('product_id', $id)->get();
        foreach ($images as $image) {
            Storage::delete($image->path); // Xóa tệp tin từ storage
            $image->delete(); // Xóa bản ghi từ database
        }

        // Xóa sản phẩm
        $product->delete();

        return response()->json(['success' => 'Sản phẩm đã được xóa thành công.']);
    }

    public function uploadBatchImages(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'images.*' => 'file|image|max:2048', // Mỗi file phải là hình ảnh và không quá 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        // Giả định rằng 'ids' được gửi như một chuỗi JSON
        $ids = json_decode($request->input('ids'), true);
        $images = $request->file('images');

        // Kiểm tra xem $images có phải là mảng không
        if (!is_array($images)) {
            $images = [$images]; // Chuyển đổi thành mảng nếu chỉ có một file
        }

        if (!is_array($ids) || count($ids) !== count($images)) {
            return response()->json([
                'error' => 'Số lượng IDs và ảnh không khớp.',
                'ids' => count($ids),
                'images' => count($images)
            ], 400);
        }

        foreach ($ids as $index => $id) {
            if (isset($images[$index])) {
                $image = $images[$index];
                $path = $image->store('public/images');
                $imageName = $image->getClientOriginalName();

                ImageModel::create([
                    'product_id' => $id,
                    'path' => $path,
                    'name' => $imageName
                ]);
            }
        }

        return response()->json(['message' => 'Ảnh đã được tải lên thành công!'], 200);
    }

    public function getProductVariants($id)
    {
        $product = Product::with('variants')->find($id);

        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không tồn tại.'], 404);
        }

        $variants = $product->variants;

        if ($variants->isEmpty()) {
            return response()->json(['message' => 'Sản phẩm chưa có biến thể.'], 200);
        }

        return response()->json($variants);
    }

    public function updateOrDeleteVariant(Request $request, $productId, $variantId)
    {
        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không tồn tại.'], 404);
        }

        $variant = ProductVariant::where('product_id', $productId)->where('id', $variantId)->first();
        if (!$variant) {
            return response()->json(['error' => 'Biến thể không tồn tại.'], 404);
        }

        $quantityToRemove = (int) $request->input('quantity');
        if ($quantityToRemove < 0) {
            return response()->json(['error' => 'Số lượng không hợp lệ.'], 400);
        }

        // Khác với lô hạn dùng: biến thể về 0 vẫn giữ lại vì nó là một mục
        // trong danh mục sản phẩm (size/màu vẫn tồn tại, chỉ là hết hàng).
        $variant->quantity = max(0, $variant->quantity - $quantityToRemove);
        $variant->save();

        $total = ProductVariant::where('product_id', $product->id)->sum('quantity');
        $product->status = $total > 0 ? 1 : 2;
        $product->save();

        return response()->json(['success' => 'Số lượng biến thể đã được cập nhật.']);
    }
}
