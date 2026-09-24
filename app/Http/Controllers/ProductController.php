<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\ProductStyle;
use App\Models\ProductVariant;
use App\Models\ImageModel;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\ProductMediaService;
use App\Support\ProductPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    private ?bool $variantPauseColumnAvailable = null;
    private ?bool $variantAttributeLabelsColumnAvailable = null;

    public function __construct(private readonly ProductMediaService $productMedia)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Categories::where('status', 1)->orderBy('name')->get();
        $supplier = Supplier::where('status', 1)->orderBy('supplier_name')->get();
        $products = $this->buildFilterQuery($request)->paginate(15)->withQueryString();

        return view("product.index", compact("products", "categories", "supplier"));
    }

    public function getData(Request $request)
    {
        $products = $this->buildFilterQuery($request)->paginate(15)->withQueryString();

        return view("product.data", compact("products"));
    }

    public function search(Request $request)
    {
        return $this->getData($request);
    }

    /**
     * Xây dựng câu truy vấn lọc sản phẩm theo từ khóa, danh mục và trạng thái tồn kho / nổi bật.
     */
    private function buildFilterQuery(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));
        $categoryId = $request->input('category_id') ?: $request->input('categories_id');
        $filter = $request->input('filter'); // all, in_stock, out_of_stock, featured

        $query = Product::with(['supplier', 'category', 'productImage', 'variants.style', 'styles.image'])
            ->withSum('variants', 'quantity')
            ->latest('id');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('product_name', 'ilike', "%{$keyword}%")
                  ->orWhere('brand', 'ilike', "%{$keyword}%")
                  ->orWhere('material', 'ilike', "%{$keyword}%")
                  ->orWhere('audience', 'ilike', "%{$keyword}%")
                  ->orWhereHas('variants', function ($vq) use ($keyword) {
                      $vq->where('sku', 'ilike', "%{$keyword}%")
                         ->orWhere('size', 'ilike', "%{$keyword}%")
                         ->orWhere('color', 'ilike', "%{$keyword}%");
                  })
                  ->orWhereHas('styles', fn($sq) => $sq->where('name', 'ilike', "%{$keyword}%"));
            });
        }

        if (!empty($categoryId)) {
            $query->where('categories_id', $categoryId);
        }

        if ($filter === 'featured') {
            $query->where('is_featured', true);
        } elseif ($filter === 'in_stock') {
            // Còn hàng: Không quản lý tồn hoặc có biến thể số lượng > 0
            $query->where(function ($q) {
                $q->where('manage_stock', false)
                  ->orWhereHas('variants', fn($vq) => $vq->where('quantity', '>', 0));
            });
        } elseif ($filter === 'out_of_stock') {
            // Hết hàng: Quản lý tồn và không có biến thể nào có số lượng > 0
            $query->where('manage_stock', true)
                  ->whereDoesntHave('variants', fn($vq) => $vq->where('quantity', '>', 0));
        }

        return $query;
    }


    public function getProductById(string $id)
    {
        $products = Product::with(['supplier', 'category', 'productImage', 'variants.style', 'styles.image', 'styles.variants'])
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

        if (!$this->productMedia->delete($image)) {
            return response()->json([
                'error' => 'Ảnh này đang được một mẫu sản phẩm sử dụng. Hãy đổi ảnh của mẫu trước khi xóa.',
            ], 422);
        }
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
        $data = $this->validatedProductData($request);

        DB::beginTransaction();
        try {
            $data['barcode'] = Str::uuid()->toString();
            $data['slug'] = $this->uniqueSlug($data['product_name']);
            $data['is_featured'] = $request->boolean('is_featured');
            $data['manage_stock'] = $request->boolean('manage_stock');
            // Tồn kho nằm ở biến thể; trạng thái được đồng bộ lại sau khi lưu biến thể.
            $data['status'] = 2;

            $product = Product::create($data);

            $this->storeMedia($product, $request);
            $this->syncPinnedMedia($product, $request->input('pin_image'));
            $totalQuantity = $this->syncStylesAndVariants($product, $request, $request->input('styles', []));
            $this->syncProductStatus($product, $totalQuantity);

            DB::commit();
            return response()->json(['success' => 'Sản phẩm đã được thêm thành công!']);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Thêm sản phẩm thất bại.', ['exception' => $e]);
            return response()->json([
                'error' => 'Chưa thể thêm sản phẩm. Sản phẩm chưa được lưu; hãy kiểm tra danh mục, giá bán và các SKU rồi thử lại. Giá vốn và ảnh riêng của nhóm có thể để trống.',
            ], 500);
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

        $data = $this->validatedProductData($request, $product);

        DB::beginTransaction();
        try {
            if ($product->product_name !== $data['product_name']) {
                $data['slug'] = $this->uniqueSlug($data['product_name'], $product->id);
            }
            $data['is_featured'] = $request->boolean('is_featured');
            $data['manage_stock'] = $request->boolean('manage_stock');

            $product->update($data);

            // Phải lưu media trước: ảnh đại diện có thể chính là ảnh vừa upload,
            // đổi ghim trước khi lưu thì không tìm thấy tên file nên bị bỏ qua.
            $this->storeMedia($product, $request);
            $this->syncPinnedMedia($product, $request->input('pin_image'));
            $totalQuantity = $this->syncStylesAndVariants($product, $request, $request->input('styles', []));
            $this->syncProductStatus($product, $totalQuantity);

            DB::commit();
            // Trả JSON vì form sửa gửi bằng AJAX; bản cũ redirect()->back() khiến JS không đọc được kết quả.
            return response()->json(['success' => 'Sản phẩm đã được cập nhật thành công!']);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Sửa sản phẩm thất bại.', ['exception' => $e]);
            return response()->json([
                'error' => 'Chưa thể cập nhật sản phẩm. Các thay đổi chưa được lưu; hãy kiểm tra thông tin, giá bán và các SKU rồi thử lại. Giá vốn và ảnh riêng của nhóm có thể để trống.',
            ], 500);
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
            // Nhãn này do từng brand tự đặt (ví dụ: "Mọi không gian", "Quà tặng",
            // "Nam", "Trẻ em"). Không giới hạn theo một ngành hàng cố định.
            'audience' => 'nullable|string|max:100',
            'unit' => 'nullable|max:30',
            'import_price' => 'nullable|integer|min:0',
            'sell_price' => 'required|integer|min:0',
            'discount_price' => 'nullable|integer|min:0|lte:sell_price',
            'is_featured' => 'nullable|boolean',
            'manage_stock' => 'nullable|boolean',
            'variant_attribute_labels' => 'nullable|array',
            'variant_attribute_labels.style' => 'nullable|string|max:40',
            'variant_attribute_labels.color' => 'nullable|string|max:40',
            'variant_attribute_labels.size' => 'nullable|string|max:40',

            // Media: ảnh tối đa 5MB, video tối đa 50MB (giới hạn theo từng file ở dưới).
            'media' => 'nullable|array',
            'media.*' => [
                'file',
                'max:51200',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime,video/webm',
            ],
            'pin_image' => 'nullable|string',

            // Một ảnh nằm ở cấp mẫu và được toàn bộ màu/size bên trong dùng chung.
            'styles' => 'nullable|array|min:1',
            'styles.*.id' => 'nullable|integer|exists:product_styles,id',
            'styles.*.name' => 'required_with:styles|string|max:100',
            'styles.*.image_id' => 'nullable|integer|exists:image_models,id',
            'styles.*.image' => 'nullable|file|image|max:5120',
            'styles.*.variants' => 'required_with:styles|array|min:1',
            'styles.*.variants.*.id' => 'nullable|integer|exists:product_variants,id',
            'styles.*.variants.*.paused' => 'nullable|boolean',
            'styles.*.variants.*.size' => 'nullable|string|max:50',
            'styles.*.variants.*.color' => 'nullable|string|max:50',
            'styles.*.variants.*.quantity' => 'nullable|integer|min:0',
            'styles.*.variants.*.price_override' => 'nullable|integer|min:0',

            // Hợp đồng cũ vẫn được nhận trong giai đoạn web/app quản trị nâng cấp lệch nhau.
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer|exists:product_variants,id',
            'variants.*.size' => 'nullable|string|max:50',
            'variants.*.color' => 'nullable|string|max:50',
            'variants.*.quantity' => 'nullable|integer|min:0',
            'variants.*.price_override' => 'nullable|integer|min:0',
        ];
    }

    /** Validate product fields and translate the selected discount mode to its stored sale price. */
    private function validatedProductData(Request $request, ?Product $product = null): array
    {
        $data = $request->validate($this->productRules($product));
        $input = $request->all();

        // Các ô tiền trong form có thể để trống. Chuẩn hóa rõ ràng về NULL
        // trước khi ghi PostgreSQL (tránh gửi chuỗi rỗng vào cột số).
        foreach (['import_price', 'discount_price'] as $field) {
            if (array_key_exists($field, $data) && blank($data[$field])) {
                $data[$field] = null;
            }
        }

        // Nhãn là dữ liệu hiển thị, còn giá trị và tồn kho tiếp tục dùng cấu
        // trúc biến thể hiện có. Nhờ vậy sản phẩm cũ không bị ảnh hưởng.
        if ($this->supportsVariantAttributeLabelsColumn()) {
            $data['variant_attribute_labels'] = Product::normalizeVariantAttributeLabels(
                $data['variant_attribute_labels'] ?? null,
            );
        } else {
            // Cho phép triển khai code trước migration mà không làm lỗi tạo/sửa
            // sản phẩm. Migration nullable sẽ được áp dụng riêng sau khi deploy.
            unset($data['variant_attribute_labels']);
        }

        // Keep accepting discount_price from older clients while the admin form
        // sends discount_type + discount_value.
        if (array_key_exists('discount_type', $input) || array_key_exists('discount_value', $input)) {
            $discountType = $request->input('discount_type');
            $discountInput = $request->validate([
                'discount_type' => 'required|in:amount,percent',
                'discount_value' => $discountType === 'percent'
                    ? 'nullable|numeric|min:0|max:100'
                    : 'nullable|integer|min:0|max:' . (int) $data['sell_price'],
            ]);

            $value = $discountInput['discount_value'] ?? null;
            $data['discount_price'] = $value === null || $value === ''
                ? null
                : ProductPricing::discountedPrice(
                    (int) $data['sell_price'],
                    $discountInput['discount_type'],
                    $value,
                );
        }

        return $data;
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

        foreach ($request->file('media') as $file) {
            $isVideo = str_starts_with((string) $file->getMimeType(), 'video/');
            $name = $file->getClientOriginalName();
            $shouldPin = !$isVideo && !$hasPinned && $request->pin_image === $name;

            $media = $this->productMedia->store($product, $file, [
                'sort_order' => ++$sortOrder,
                // Video không dùng làm ảnh đại diện được, nên chỉ ảnh mới được ghim.
                'is_pined' => $shouldPin,
            ]);

            // Tránh ghim hai media khi người dùng gửi lên nhiều file trùng tên.
            $hasPinned = $hasPinned || $media->is_pined || $shouldPin;
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
     * Đồng bộ các nhóm mẫu và biến thể con. Không xóa dòng bị thiếu khỏi payload:
     * variant_id đã có thể nằm trong giỏ/đơn cũ; muốn ngừng bán chỉ cần đưa tồn về 0.
     */
    private function syncStylesAndVariants(Product $product, Request $request, array $styles): int
    {
        if ($styles === []) {
            $fallback = $product->styles()->firstOrCreate(
                ['name_key' => 'mẫu mặc định'],
                [
                    'name' => 'Mẫu mặc định',
                    'image_model_id' => $product->thumbnail?->id,
                    'sort_order' => 0,
                ],
            );
            $styles = [[
                'id' => $fallback->id,
                'name' => $fallback->name,
                'image_id' => $fallback->image_model_id,
                'variants' => $request->input('variants', []),
            ]];
        }

        $existingStyles = $product->styles()->get()->keyBy('id');
        $existingVariants = ProductVariant::where('product_id', $product->id)->get()->keyBy('id');
        $seenStyleNames = [];
        $seenCombinations = [];
        $variantSort = 0;
        $mediaSort = (int) ImageModel::where('product_id', $product->id)->max('sort_order');

        $styleSort = 0;

        // Giữ nguyên key do form gửi lên để tìm đúng UploadedFile và trả lỗi
        // đúng ô. Khi người dùng xóa một thẻ mẫu, các key JS có thể bị hở
        // (ví dụ styles[0], styles[2]); array_values() sẽ làm lệch file của mẫu.
        foreach ($styles as $styleIndex => $styleRow) {
            $styleName = preg_replace('/\s+/u', ' ', trim((string) ($styleRow['name'] ?? '')));
            $nameKey = mb_strtolower($styleName, 'UTF-8');

            if ($styleName === '') {
                throw ValidationException::withMessages([
                    "styles.$styleIndex.name" => 'Mỗi mẫu phải có tên.',
                ]);
            }
            if (isset($seenStyleNames[$nameKey])) {
                throw ValidationException::withMessages([
                    "styles.$styleIndex.name" => 'Tên mẫu bị trùng trong cùng sản phẩm.',
                ]);
            }
            $seenStyleNames[$nameKey] = true;

            $styleId = (int) ($styleRow['id'] ?? 0);
            $style = $styleId ? $existingStyles->get($styleId) : null;
            if ($styleId && !$style) {
                throw ValidationException::withMessages([
                    "styles.$styleIndex.id" => 'Mẫu không thuộc sản phẩm đang sửa.',
                ]);
            }
            $style ??= $product->styles()->where('name_key', $nameKey)->first() ?? new ProductStyle();

            $image = null;
            $uploaded = $request->file("styles.$styleIndex.image");
            if ($uploaded) {
                $image = $this->productMedia->store($product, $uploaded, [
                    'sort_order' => ++$mediaSort,
                ]);
            } elseif (!empty($styleRow['image_id'])) {
                $image = ImageModel::where('product_id', $product->id)
                    ->where('media_type', ImageModel::TYPE_IMAGE)
                    ->find((int) $styleRow['image_id']);
                if (!$image) {
                    throw ValidationException::withMessages([
                        "styles.$styleIndex.image_id" => 'Ảnh mẫu không thuộc sản phẩm đang sửa.',
                    ]);
                }
            } elseif ($style->exists && $style->image_model_id) {
                $image = $style->image;
            }

            $style->fill([
                'product_id' => $product->id,
                'image_model_id' => $image?->id,
                'name' => $styleName,
                'name_key' => $nameKey,
                'sort_order' => $styleSort++,
            ]);
            $style->save();

            // Tương tự, giữ key biến thể để thông báo validation trỏ đúng dòng
            // ngay cả khi một dòng mới đã bị xóa ở giữa danh sách.
            foreach (($styleRow['variants'] ?? []) as $variantIndex => $row) {
                $variantId = (int) ($row['id'] ?? 0);
                $isPaused = $variantId > 0 && filter_var($row['paused'] ?? false, FILTER_VALIDATE_BOOLEAN);

                if ($isPaused) {
                    $variant = $existingVariants->get($variantId);
                    if (!$variant || (int) $variant->product_style_id !== (int) $style->id) {
                        throw ValidationException::withMessages([
                            "styles.$styleIndex.variants.$variantIndex.id" => 'Biến thể không thuộc mẫu đang sửa.',
                        ]);
                    }

                    // Tạm dừng chỉ đổi tồn kho về 0; giữ màu/size cũ kể cả khi
                    // dữ liệu legacy của biến thể đó đang thiếu một trong hai.
                    $combinationKey = $style->id . '||' . mb_strtolower(
                        trim((string) $variant->color) . '||' . trim((string) $variant->size),
                        'UTF-8',
                    );
                    if (isset($seenCombinations[$combinationKey])) {
                        throw ValidationException::withMessages([
                            "styles.$styleIndex.variants.$variantIndex" => 'Tổ hợp thuộc tính bị trùng trong cùng nhóm.',
                        ]);
                    }
                    $seenCombinations[$combinationKey] = true;

                    if ((int) $variant->quantity !== 0) {
                        $variant->quantity = 0;
                    }
                    if ($this->supportsVariantPauseColumn()) {
                        $variant->is_paused = true;
                    }
                    $variant->save();
                    continue;
                }

                $size = preg_replace('/\s+/u', ' ', trim((string) ($row['size'] ?? '')));
                $color = preg_replace('/\s+/u', ' ', trim((string) ($row['color'] ?? '')));
                if ($size === '' || $color === '') {
                    throw ValidationException::withMessages([
                        "styles.$styleIndex.variants.$variantIndex" => 'Mỗi SKU cần có đủ hai lựa chọn đang thiết lập.',
                    ]);
                }

                $combinationKey = $style->id . '||' . mb_strtolower($color . '||' . $size, 'UTF-8');
                if (isset($seenCombinations[$combinationKey])) {
                    throw ValidationException::withMessages([
                        "styles.$styleIndex.variants.$variantIndex" => 'Tổ hợp thuộc tính bị trùng trong cùng nhóm.',
                    ]);
                }
                $seenCombinations[$combinationKey] = true;

                $variant = $variantId ? $existingVariants->get($variantId) : null;
                if ($variantId && (!$variant || (int) $variant->product_style_id !== (int) $style->id)) {
                    throw ValidationException::withMessages([
                        "styles.$styleIndex.variants.$variantIndex.id" => 'Biến thể không thuộc mẫu đang sửa.',
                    ]);
                }

                $variant ??= ProductVariant::where('product_style_id', $style->id)
                    ->where('size', $size)
                    ->where('color', $color)
                    ->first() ?? new ProductVariant();

                $rawPrice = $row['price_override'] ?? null;
                $variantData = [
                    'product_id' => $product->id,
                    'product_style_id' => $style->id,
                    'size' => $size,
                    'color' => $color,
                    'quantity' => max(0, (int) ($row['quantity'] ?? 0)),
                    'price_override' => ($rawPrice === null || $rawPrice === '') ? null : (int) $rawPrice,
                    'sort_order' => $variantSort++,
                ];
                if ($this->supportsVariantPauseColumn()) {
                    $variantData['is_paused'] = false;
                }
                $variant->fill($variantData);
                if (!$variant->exists) {
                    $variant->sku = $product->barcode . '-' . strtoupper(Str::random(5));
                }
                $variant->save();
            }
        }

        return (int) ProductVariant::where('product_id', $product->id)->sum('quantity');
    }

    private function supportsVariantPauseColumn(): bool
    {
        return $this->variantPauseColumnAvailable ??= Schema::hasColumn('product_variants', 'is_paused');
    }

    private function supportsVariantAttributeLabelsColumn(): bool
    {
        return $this->variantAttributeLabelsColumnAvailable ??= Schema::hasColumn(
            'products',
            'variant_attribute_labels',
        );
    }

    /**
     * Đồng bộ trạng thái còn/hết hàng từ tổng tồn kho đã tính sẵn ở syncVariants,
     * tránh chạy thêm một truy vấn SUM nữa.
     */
    private function syncProductStatus(Product $product, int $totalQuantity): void
    {
        // Hàng không theo dõi tồn kho thì luôn ở trạng thái bán được: số tồn của
        // nó không phản ánh gì, để nó tự nhảy sang "hết hàng" là chặn nhầm.
        $status = ($totalQuantity > 0 || !$product->manage_stock) ? 1 : 2;

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
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không tồn tại.'], 404);
        }

        // Product dùng SoftDeletes: giữ nguyên media và biến thể để có thể khôi
        // phục sản phẩm/đọc đúng đơn cũ. Đặc biệt không xóa object tại đây vì
        // đường dẫn content-addressed có thể đang được sản phẩm khác dùng chung.
        $product->delete();

        return response()->json(['success' => 'Sản phẩm đã được xóa thành công.']);
    }

    public function uploadBatchImages(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'ids' => 'required|string',
            'images' => 'required|array|min:1',
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
                'ids' => is_array($ids) ? count($ids) : 0,
                'images' => count($images)
            ], 400);
        }

        $products = Product::whereIn('id', $ids)->get()->keyBy('id');
        if ($products->count() !== count(array_unique(array_map('intval', $ids)))) {
            return response()->json(['error' => 'Có sản phẩm không tồn tại.'], 422);
        }

        $sortOrders = ImageModel::whereIn('product_id', $ids)
            ->selectRaw('product_id, COALESCE(MAX(sort_order), 0) AS max_sort')
            ->groupBy('product_id')
            ->pluck('max_sort', 'product_id')
            ->map(fn ($value) => (int) $value)
            ->all();

        foreach ($ids as $index => $id) {
            if (isset($images[$index])) {
                $product = $products->get((int) $id);
                $sortOrders[$product->id] = ($sortOrders[$product->id] ?? 0) + 1;
                $this->productMedia->store($product, $images[$index], [
                    'sort_order' => $sortOrders[$product->id],
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
