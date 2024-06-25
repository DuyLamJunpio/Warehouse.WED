<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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


    public function index(Request $request)
    {
        $perPage = 15;
        $products = Product::with(['supplier', 'category', 'productImage', 'location'])
            ->withSum('expiries', 'quantity_exp')
            ->paginate($perPage);

        return response()->json($products);
    }

    public function search(Request $request)
    {
        $keyword = trim($request->input('keyword')); // Lấy từ khóa từ request và loại bỏ khoảng trắng thừa
        $supplierId = $request->input('supplier_id'); // Lấy ID nhà cung cấp từ request, có thể là null

        // Tạo khóa cache dựa trên từ khóa và có thể là supplier_id nếu có
        $key = "search_" . ($keyword ?: "all") . ($supplierId ? "_supplier_{$supplierId}" : "");

        $products = Cache::remember($key, 60 * 60, function () use ($keyword, $supplierId) {
            $query = Product::with(['supplier', 'category', 'productImage', 'location'])
                ->withSum('expiries', 'quantity_exp');

            if (!empty ($keyword)) {
                $query->where('product_name', 'like', "%{$keyword}%");
            }

            if (!empty ($supplierId)) {
                $query->where('supplier_id', $supplierId); // Thêm điều kiện lọc theo supplier_id nếu có
            }

            return $query->get();
        });

        return response()->json($products);
    }

    public function getProductsBySupplier(Request $request)
    {
        $perPage = 15;
        $supplierId = $request->input('supplier_id'); // Lấy ID nhà cung cấp từ request

        $query = Product::with(['supplier', 'category', 'productImage', 'location'])
            ->withSum('expiries', 'quantity_exp')->whereHas('supplier', function ($query) {
                $query->whereNull('deleted_at');
            });

        if (!empty($supplierId)) {
            $query->where('supplier_id', $supplierId); // Lọc sản phẩm theo supplier_id nếu có
        }

        $products = $query->paginate($perPage); // Lấy sản phẩm từ database

        return response()->json($products);
    }

    public function filterBySupplier(Request $request)
    {
        $supplierId = $request->input('supplier_id'); // Lấy ID của nhà cung cấp từ request

        if (!empty($supplierId)) {
            $products = Product::with(['supplier', 'category', 'productImage', 'location'])
                ->withSum('expiries', 'quantity_exp')
                ->where('supplier_id', $supplierId)
                ->paginate(15);

            if ($products->isEmpty()) {
                return response()->json(['message' => 'Không tìm thấy sản phẩm nào từ nhà cung cấp này.'], 404);
            }
        } else {
            return response()->json(['error' => 'ID nhà cung cấp không được cung cấp.'], 400);
        }

        return response()->json($products);
    }

    public function filterByCategory(Request $request)
    {
        $categoryId = $request->input('categories_id'); // Lấy ID của danh mục từ request

        if (!empty($categoryId)) {
            $products = Product::with(['supplier', 'category', 'productImage', 'location'])
                ->withSum('expiries', 'quantity_exp')
                ->where('categories_id', $categoryId)
                ->paginate(15);

            if ($products->isEmpty()) {
                return response()->json(['message' => 'Không tìm thấy sản phẩm nào trong danh mục này.'], 404);
            }
        } else {
            return response()->json(['error' => 'ID danh mục không được cung cấp.'], 400);
        }

        return response()->json($products);
    }

    public function filterByStatus(Request $request)
    {
        $status = $request->input('status'); // Lấy trạng thái từ request

        if ($status !== null) {
            $products = Product::with(['supplier', 'category', 'productImage', 'location'])
                ->withSum('expiries', 'quantity_exp')
                ->where('status', $status)
                ->paginate(15);

            if ($products->isEmpty()) {
                return response()->json(['message' => 'Không tìm thấy sản phẩm nào với trạng thái này.'], 404);
            }
        } else {
            return response()->json(['error' => 'Trạng thái sản phẩm không được cung cấp.'], 400);
        }

        return response()->json($products);
    }

    public function getProductById(string $id)
    {
        $products = Product::with(['supplier', 'category', 'productImage', 'location'])
            ->withSum('expiries', 'quantity_exp')
            ->where('products.id', $id)
            ->get();

        return response()->json($products);
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



    public function getProductStatus()
    {
        $products = Product::with(['supplier', 'category', 'productImage', 'location'])
            ->withSum('expiries', 'quantity_exp')->whereIn('products.status', [1, 2])->get();
        return response()->json($products);
    }



    public function searchProductStatus(Request $request)
    {
        $pin_image = ImageModel::all();
        $keyword = trim($request->input('keyword')); // Lấy từ khóa từ request và loại bỏ khoảng trắng thừa
        if (!empty($keyword)) {
            $key = "search_invoice_{$keyword}"; // Tạo một khóa cache duy nhất dựa trên từ khóa
            Cache::forget($key); // Thay 'key_name' bằng khóa cache cụ thể bạn muốn xóa
            $products = Cache::remember($key, 60 * 60, function () use ($keyword) {
                return Product::with(['supplier', 'category', 'productImage', 'location'])
                    ->withSum('expiries', 'quantity_exp')
                    ->where('products.product_name', 'like', "%{$keyword}%") // Đảm bảo rằng bạn đang tìm kiếm trong cột đúng
                    ->whereIn('products.status', [1, 2]) // Lọc sản phẩm có trạng thái là 1 hoặc 2
                    ->get();
            });
        } else {
            $products = Product::with(['supplier', 'category', 'productImage', 'location'])
                ->withSum('expiries', 'quantity_exp')
                ->whereIn('products.status', [1, 2]) // Lọc sản phẩm có trạng thái là 1 hoặc 2
                ->get();
        }
        return response()->json($products);
    }

    public function searchProductSupplier(Request $request)
    {
        $keyword = trim($request->input('keyword')); // Lấy từ khóa từ request và loại bỏ khoảng trắng thừa
        if (!empty($keyword)) {
            $key = "search_supplier_{$keyword}"; // Tạo một khóa cache duy nhất dựa trên từ khóa
            Cache::forget($key); // Thay 'key_name' bằng khóa cache cụ thể bạn muốn xóa
            $products = Cache::remember($key, 60 * 60, function () use ($keyword) {
                return Product::with(['supplier', 'category', 'productImage', 'location'])
                    ->withSum('expiries', 'quantity_exp')
                    ->whereHas('supplier', function ($query) use ($keyword) {
                        $query->where('supplier_name', 'like', "%{$keyword}%");
                    })
                    ->whereIn('products.status', [1, 2])
                    ->get();
            });
        } else {
            $products = Product::with(['supplier', 'category', 'productImage', 'location'])
                ->withSum('expiries', 'quantity_exp')
                ->whereIn('products.status', [1, 2]) // Lọc sản phẩm có trạng thái là 1 hoặc 2
                ->get();
        }
        return response()->json($products);
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

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
        if ($request->filled('pin_image') || $request->pin_image != null || $request->pin_image != '') {
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


        if ($request->zone != -1 && $request->shelf != "" && $request->level != -1) {
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
        }

        return response()->json(['success', 'Sản phẩm đã được cập nhật thành công!']);
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
    public function updateProductStatus()
    {
        // Lấy tất cả các sản phẩm
        $products = Product::all();

        foreach ($products as $product) {
            // Kiểm tra xem sản phẩm có lô hàng nào không
            $expiryCount = Expiry::where('product_id', $product->id)->count();

            // Nếu không có lô hàng nào, cập nhật trạng thái của sản phẩm thành 2
            if ($expiryCount == 0) {
                $product->status = 2;
                $product->save();
            }
        }

        return response()->json(['success' => 'Trạng thái của các sản phẩm đã được cập nhật.']);
    }
}
