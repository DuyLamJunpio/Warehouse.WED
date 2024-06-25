<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Expiry;
use App\Models\ImageModel;
use App\Models\Product;
use App\Models\ProductLocation;
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
        $products = Product::with(['supplier', 'category', 'productImage', 'location'])
            ->withSum('expiries', 'quantity_exp')->paginate($perPage);

        $zones = ProductLocation::whereNull('product_id')
            ->select('zone')
            ->distinct()
            ->orderBy('zone')
            ->get();

        return view("product.index", compact("products", "categories", "supplier", "zones"));
    }

    public function getData()
    {
        $perPage = 10;
        $products = Product::with(['supplier', 'category', 'productImage', 'location'])
            ->withSum('expiries', 'quantity_exp')->paginate($perPage);

        return view("product.data", compact("products"));
    }

    public function search(Request $request)
    {
        $pin_image = ImageModel::all();
        $keyword = trim($request->input('keyword')); // Lấy từ khóa từ request và loại bỏ khoảng trắng thừa
        if (!empty($keyword)) {
            $key = "search_{$keyword}"; // Tạo một khóa cache duy nhất dựa trên từ khóa
            $products = Cache::remember($key, 60 * 60, function () use ($keyword) {
                return DB::table('products')->withSum('expiries', 'quantity_exp')
                    ->join('categories', 'products.categories_id', '=', 'categories.id')
                    ->join('suppliers', 'products.supplier_id', '=', 'suppliers.id') // Thêm join với bảng suppliers
                    ->where('products.product_name', 'like', "%{$keyword}%") // Đảm bảo rằng bạn đang tìm kiếm trong cột đúng
                    ->select('products.*', 'categories.name AS categories', 'suppliers.supplier_name AS supplier')
                    ->get();
            });
        } else {
            $perPage = 15;
            $products = Product::with(['supplier', 'category', 'productImage', 'location'])->withSum('expiries', 'quantity_exp')->paginate($perPage);
        }
        return view("product.data", compact("products"));
    }

    public function getProductById(string $id)
    {
        $products = Product::with(['supplier', 'category', 'productImage', 'location'])
            ->withSum('expiries', 'quantity_exp')
            ->where('products.id', $id)
            ->get();

        return $products;
    }

    public function getImageUrl(string $id)
    {
        $imageUrl = ImageModel::where("product_id", $id)->get();
        $count = $imageUrl->count();
        $paths = [];
        for ($i = 0; $i < $count; $i++) {
            $paths[$i] = Storage::url($imageUrl[$i]->path);
        }

        return response()->json([
            'imageUrl' => $imageUrl,
            'paths' => $paths,
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
        $request->validate([
            'images' => 'nullable|array',
            'images.*' => 'file|image|max:2048',
            'pin_image' => 'nullable',
            'product_name' => 'required|max:255',
            'sell_price' => 'required|integer|min:0',
            'import_price' => 'required|integer|min:0',
            'unit' => 'required|max:30',
            'supplier_id' => 'required',
            'categories_id' => 'required',
        ]);

        $params = $request->except('_token');
        $params['barcode'] = Str::uuid()->toString();
        $params['status'] = 2;
        $params['total_quantity'] = 0;
        $product = Product::create($params);

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('public/images');
                if ($request->pin_image == $image->getClientOriginalName()) {
                    $isPined = true;
                } else {
                    $isPined = false;
                }
                $images[] = new ImageModel([
                    'path' => $path,
                    'name' => $image->getClientOriginalName(),
                    'is_pined' => $isPined
                ]);
            }
            $product->imageModel()->saveMany($images);
        }

        if ($request->zone && $request->shelf && $request->level) {
            $code = 'K' . $request->zone . '-' . $request->shelf . '-' . $request->level;
            $location = ProductLocation::where('code', $code)->first();

            if ($location) {
                if ($location->product_id === null) {
                    $location->product_id = $product->id;
                    $location->save();
                    return response()->json(['success' => 'Sản phẩm đã được thêm thành công!']);
                } else {
                    return response()->json(['error' => 'Vị trí này đã có sản phẩm.'], 409);
                }
            } else {
                return response()->json(['error' => 'Không tìm thấy vị trí phù hợp.'], 404);
            }
        }

        return response()->json(['success' => 'Sản phẩm đã được thêm thành công!']);
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

        $request->validate([
            'images' => 'nullable|array',
            'images.*' => 'file|image|max:2048',
            'pin_image' => 'nullable',
            'product_name' => 'required|max:255',
            'sell_price' => 'required|integer|min:0',
            'import_price' => 'required|integer|min:0',
            'unit' => 'required|max:30',
            'supplier_id' => 'required',
            'categories_id' => 'required',
        ]);

        $params = $request->except(['_token', 'images']);
        $product->update($params);

        // Xử lý ảnh ghim mà không cần tải ảnh mới
        if ($request->filled('pin_image')) {
            // Xóa đánh dấu ghim trên tất cả các ảnh hiện tại
            ImageModel::where('product_id', $product->id)->update(['is_pined' => false]);

            // Đánh dấu ảnh mới là ghim
            $pinImage = ImageModel::where('product_id', $product->id)
                ->where('name', $request->pin_image)
                ->first();
            if ($pinImage) {
                $pinImage->is_pined = true;
                $pinImage->save();
            }
        }

        if ($request->hasFile('images')) {
            // Xóa đánh dấu ghim trên tất cả các ảnh hiện tại
            ImageModel::where('product_id', $product->id)->update(['is_pined' => false]);

            // Thêm hình ảnh mới
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('public/images');
                $isPined = ($request->pin_image == $image->getClientOriginalName());
                $images[] = new ImageModel([
                    'product_id' => $product->id,
                    'path' => $path,
                    'name' => $image->getClientOriginalName(),
                    'is_pined' => $isPined
                ]);
            }
            $product->imageModel()->saveMany($images);
        }

        if ($request->zone && $request->shelf && $request->level) {
            // Tìm vị trí hiện tại của sản phẩm
            $old_location = ProductLocation::where('product_id', $product->id)->first();
            if ($old_location) {
                $old_location->product_id = null;
                $old_location->save();
            }

            // Tìm vị trí mới dựa trên zone, shelf, và level
            $new_location = ProductLocation::where('zone', $request->zone)
                                           ->where('shelf', $request->shelf)
                                           ->where('level', $request->level)
                                           ->first();

            if ($new_location && $new_location->product_id === null) {
                $new_location->product_id = $product->id;
                $new_location->save();
            } else {
                return response()->json(['error' => 'Vị trí mới không hợp lệ hoặc đã được sử dụng.'], 422);
            }
        } else {
            return response()->json(['error' => 'Thông tin vị trí không đầy đủ.'], 400);
        }

        return redirect()->back()->with('success', 'Sản phẩm đã được cập nhật thành công!');
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

    public function getProductExpiries($id)
    {
        $product = Product::with('expiries')->find($id);

        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không tồn tại.'], 404);
        }

        $expiries = $product->expiries;

        // Kiểm tra xem có lô hàng hết hạn nào không
        if ($expiries->isEmpty()) {
            return response()->json(['message' => 'Sản phẩm hết hàng.'], 200);
        }

        return response()->json($expiries);
    }

    public function updateOrDeleteExpiry(Request $request, $productId, $expiryId)
    {
        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không tồn tại.'], 404);
        }

        $expiry = Expiry::where('product_id', $productId)->where('id', $expiryId)->first();
        if (!$expiry) {
            return response()->json(['error' => 'Lô hàng không tồn tại.'], 404);
        }

        $quantityToRemove = $request->input('quantity');
        if ($quantityToRemove < 0) {
            return response()->json(['error' => 'Số lượng không hợp lệ.'], 400);
        }

        if ($quantityToRemove >= $expiry->quantity_exp) {
            // Nếu số lượng cần xóa bằng hoặc lớn hơn số lượng trong lô, xóa lô
            $expiry->delete();
            return response()->json(['success' => 'Lô hàng đã được xóa.']);
        } else {
            // Nếu số lượng cần xóa nhỏ hơn, cập nhật số lượng mới
            $expiry->quantity_exp -= $quantityToRemove;
            $expiry->save();
            return response()->json(['success' => 'Số lượng trong lô đã được cập nhật.']);
        }
    }
}
