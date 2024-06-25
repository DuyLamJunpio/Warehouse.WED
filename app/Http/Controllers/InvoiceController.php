<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expiry;
use App\Models\Invoice;
use App\Models\ImageModel;
use App\Models\Product;
use App\Models\ProductInvoice;
use App\Models\Supplier;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = 10;
        $invoices = DB::table('invoices')
            ->join('users', 'invoices.user_id', '=', 'users.id')
            ->select('invoices.*', 'users.name AS username')
            ->whereIn('invoices.pay_status', [0, 1, 2])
            ->paginate($perPage);

        $customer = Customer::where('status', 0)->get();
        $user = User::all();
        $supplier = Supplier::where('status', 1)->withTrashed()->get();
        $products = Product::with(['supplier', 'category', 'productImage', 'location'])
            ->withSum('expiries', 'quantity_exp')
            ->whereIn('products.status', [1, 2])
            ->whereHas('supplier', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->get();

        return view("invoice.index", compact("invoices", "customer", "products", "supplier", "user"));
    }

    public function filter($value = null)
    {
        $perPage = 10;
        $query = DB::table('invoices')
            ->join('users', 'invoices.user_id', '=', 'users.id')
            ->select('invoices.*', 'users.name AS username')
            ->whereIn('invoices.pay_status', [0, 1, 2]);

        if (isset($value)) {
            $query->where('invoice_type', $value);
        }

        $invoices = $query->paginate($perPage);

        $customer = Customer::where('status', 0)->get();
        $user = User::all();
        $supplier = Supplier::where('status', 1)->get();
        $products = Product::with(['supplier', 'category', 'productImage', 'location'])->withSum('expiries', 'quantity_exp')->whereIn('products.status', [1, 2])->get();

        return view("invoice.data", compact("invoices", "customer", "products", "supplier", "user"));
    }

    public function filter_pay_status($value = null)
    {
        $perPage = 10;
        $query = DB::table('invoices')
            ->join('users', 'invoices.user_id', '=', 'users.id')
            ->select('invoices.*', 'users.name AS username');

        if (isset($value)) {
            $query->where('pay_status', $value);
        }

        $invoices = $query->paginate($perPage);

        $customer = Customer::where('status', 0)->get();
        $user = User::all();
        $supplier = Supplier::where('status', 1)->get();
        $products = Product::with(['supplier', 'category', 'productImage', 'location'])->withSum('expiries', 'quantity_exp')->get();

        return view("invoice.data", compact("invoices", "customer", "products", "supplier", "user"));
    }

    public function getProductSupplier(string $id = null)
    {
        $query = Product::with(['supplier', 'category', 'productImage', 'location'])->withSum('expiries', 'quantity_exp');

        // Thêm điều kiện truy vấn chỉ khi $id được cung cấp
        if (isset($id)) {
            $query->where('products.supplier_id', $id);
        }

        $products = $query->whereIn('products.status', [1, 2])->get();

        return view("invoice.list_product", compact("products"));
    }

    public function getProduct()
    {
        $products = Product::with(['supplier', 'category', 'productImage', 'location'])->withSum('expiries', 'quantity_exp')->whereIn('products.status', [1, 2])->get();
        return view("invoice.list_product", compact("products"));
    }

    public function searchProduct(Request $request)
    {
        $pin_image = ImageModel::all();
        $keyword = trim($request->input('keyword')); // Lấy từ khóa từ request và loại bỏ khoảng trắng thừa
        if (!empty($keyword)) {
            $key = "search_invoice_{$keyword}"; // Tạo một khóa cache duy nhất dựa trên từ khóa
            Cache::forget($key); // Thay 'key_name' bằng khóa cache cụ thể bạn muốn xóa
            $products = Cache::remember($key, 60 * 60, function () use ($keyword) {
                return Product::with(['supplier', 'category', 'productImage', 'location'])->withSum('expiries', 'quantity_exp')
                    ->where('products.product_name', 'like', "%{$keyword}%") // Đảm bảo rằng bạn đang tìm kiếm trong cột đúng
                    ->whereIn('products.status', [1, 2]) // Lọc sản phẩm có trạng thái là 1 hoặc 2
                    ->get();
            });
        } else {
            $products = Product::with(['supplier', 'category', 'productImage', 'location'])->withSum('expiries', 'quantity_exp')
                ->whereIn('products.status', [1, 2]) // Lọc sản phẩm có trạng thái là 1 hoặc 2
                ->get();
        }
        return view("invoice.list_product", compact("products"));
    }

    public function searchSupplier(Request $request)
    {
        $pin_image = ImageModel::all();
        $keyword = trim($request->input('keyword')); // Lấy từ khóa từ request và loại bỏ khoảng trắng thừa
        if (!empty($keyword)) {
            $key = "search_supplier_{$keyword}"; // Tạo một khóa cache duy nhất dựa trên từ khóa
            Cache::forget($key); // Thay 'key_name' bằng khóa cache cụ thể bạn muốn xóa
            $products = Cache::remember($key, 60 * 60, function () use ($keyword) {
                return Product::with(['supplier', 'category', 'productImage', 'location'])->withSum('expiries', 'quantity_exp')
                    ->whereHas('supplier', function ($query) use ($keyword) {
                        $query->where('supplier_name', 'like', "%{$keyword}%");
                    })
                    ->whereIn('products.status', [1, 2])
                    ->get();
            });
        } else {
            $products = Product::with(['supplier', 'category', 'productImage', 'location'])->withSum('expiries', 'quantity_exp')
                ->whereIn('products.status', [1, 2]) // Lọc sản phẩm có trạng thái là 1 hoặc 2
                ->get();
        }
        return view("invoice.list_product", compact("products"));
    }

    public function getProductById(string $id)
    {
        $products = Product::with(['supplier', 'category', 'productImage', 'location'])->withSum('expiries', 'quantity_exp')
            ->where('products.id', $id)
            ->get();

        return $products;
    }

    public function getInvoiceDetails(string $id)
    {
        // Tải hóa đơn và các mối quan hệ liên quan của sản phẩm
        $invoice = Invoice::with([
            'productInvoices.product.supplier', // Tải nhà cung cấp của sản phẩm
            'productInvoices.product.category', // Tải danh mục của sản phẩm
            'productInvoices.product.imageModel', // Tải mô hình hình ảnh của sản phẩm
            'productInvoices.product.location' // Tải vị trí của sản phẩm
        ])->withSum('expiries', 'quantity_exp')->find($id);

        if (!$invoice) {
            return response()->json(['error' => 'Hóa đơn không tồn tại.'], 404);
        }

        return $invoice;
    }

    public function store(Request $request)
    {
        if ($request->isMethod('POST') || $request->ajax() || $request->wantsJson()) {
            $products = $request->input('products');

            $validator = Validator::make($request->all(), [
                'discount' => 'nullable|numeric|min:0',
                'products' => 'required|array',
                'note' => 'nullable',
                'term' => 'nullable',
                'signature_name' => 'required|string|max:255',
                'signature' => 'file|image|max:2048',
                'invoice_type' => 'required',
                'pay_status' => 'required',
                'user_id' => 'required',
                'total_amount' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            DB::beginTransaction();
            try {
                $params = $request->except('_token');

                if ($request->invoice_type == 0) {
                    $params['customer_id'] = null;
                } else {
                    $params['supplier_id'] = null;
                }

                if ($request->hasFile('signature')) {
                    $image = $request->file('signature');
                    $filename = $image->store('public/images');
                    $params['signature'] = $filename;
                }

                $invoice = Invoice::create($params);
                if (!$invoice) {
                    throw new Exception('Thêm hóa đơn không thành công.');
                }

                foreach ($products as $product) {
                    DB::table('product_invoices')->insert([
                        'invoice_id' => $invoice->id,
                        'product_id' => $product['product_id'],
                        'quantity' => $product['quantity'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $productModel = Product::find($product['product_id']);
                    if (!$productModel) {
                        throw new Exception('Sản phẩm không tồn tại.');
                    }

                    if ($request->invoice_type == 0) { // Nhập hàng
                        $expiry = Expiry::firstOrNew(
                            ['product_id' => $product['product_id'], 'expiry_date' => $product['expiry']],
                            ['quantity_exp' => 0]
                        );
                        $expiry->quantity_exp += $product['quantity'];
                        $result = $expiry->save();
                        if ($result) {
                            $productModel->status = 1;
                        }
                    } else { // Xuất hàng
                        $expiries = Expiry::where('product_id', $product['product_id'])
                            ->orderBy('expiry_date', 'asc')
                            ->get();
                        $remainingQuantity = $product['quantity'];

                        foreach ($expiries as $expiry) {
                            if ($remainingQuantity <= 0)
                                break;

                            $deductQuantity = min($expiry->quantity_exp, $remainingQuantity);
                            $expiry->quantity_exp -= $deductQuantity;
                            $remainingQuantity -= $deductQuantity;

                            if ($expiry->quantity_exp == 0) {
                                $expiry->delete(); // Xóa bản ghi nếu số lượng bằng 0
                            } else {
                                $result = $expiry->save();
                                if ($result) {
                                    $productModel->status = 2;
                                }
                            }
                        }
                    }
                    $productModel->save();
                }

                DB::commit();
                return response()->json(['success' => 'Hóa đơn đã được thêm thành công!']);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return response()->json(['error' => 'Hóa đơn không tồn tại.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'discount' => 'nullable|numeric|min:0',
            'note' => 'nullable',
            'term' => 'nullable',
            'signature_name' => 'required|string|max:255',
            'signature' => 'file|image|max:2048',
            'pay_status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        DB::beginTransaction();
        try {
            $params = $request->except(['_token', 'products']);
            $params['user_id'] = $invoice->user_id;

            if ($request->hasFile('signature')) {
                $resultImg = Storage::delete($invoice->signature);
                if ($resultImg) {
                    $image = $request->file('signature');
                    $filename = $image->store('public/images');
                    $params['signature'] = $filename;
                } else {
                    $params['signature'] = $invoice->signature;
                }
            }

            $invoice->update($params);

            // // Cập nhật sản phẩm trong hóa đơn
            // $products = $request->input('products');
            // foreach ($products as $product) {
            //     $productInvoice = ProductInvoice::where('invoice_id', $invoice->id)
            //         ->where('product_id', $product['productId'])
            //         ->first();
            //     if ($productInvoice) {
            //         // Cập nhật số lượng cho sản phẩm đã tồn tại
            //         $oldQuantity = $productInvoice->quantity;
            //         $newQuantity = $product['quantity'];
            //         $productInvoice->update(['quantity' => $newQuantity]);
            //     } else {
            //         // Thêm sản phẩm mới vào hóa đơn
            //         ProductInvoice::create([
            //             'invoice_id' => $invoice->id,
            //             'product_id' => $product['productId'],
            //             'quantity' => $product['quantity']
            //         ]);
            //         $newQuantity = $product['quantity'];
            //         $oldQuantity = 0; // Không có số lượng cũ vì là sản phẩm mới
            //     }

            //     $productModel = Product::find($product['productId']);
            //     if (!$productModel) {
            //         throw new Exception('Sản phẩm không tồn tại.');
            //     }

            //     // Cập nhật tổng số lượng sản phẩm dựa trên loại hóa đơn
            //     if ($invoice->invoice_type == 0) { // Hóa đơn nhập
            //         $productModel->total_quantity += ($newQuantity - $oldQuantity);
            //     } else { // Hóa đơn xuất
            //         $productModel->total_quantity -= ($newQuantity - $oldQuantity);
            //         if ($productModel->total_quantity == 0) {
            //             $productModel->status = 2;
            //         }
            //     }
            //     $productModel->save();
            // }

            DB::commit();
            return response()->json(['success' => 'Hóa đơn đã được cập nhật thành công!', 'invoice_id' => $invoice->id]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function destroy(string $id)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return response()->json(['error' => "Hóa đơn không tồn tại"], 404);
        }

        DB::beginTransaction();
        try {
            // Cập nhật trạng thái hóa đơn
            $invoice->pay_status = 3;
            $invoice->save();

            // Xóa hóa đơn
            $invoice->delete();

            DB::commit();
            return response()->json(['success' => 'Xóa hóa đơn thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
