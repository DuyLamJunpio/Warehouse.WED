<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InvoiceController extends Controller
{

    public function index()
    {
        $perPage = 15;
        $invoices = Invoice::with([
            'productInvoices.product.productImage',
            'user',
            'supplier',
            'customer'
        ])->whereIn('pay_status', [0, 1, 2])->paginate($perPage);

        return response()->json($invoices);
    }


    public function filter($value = null)
    {
        $perPage = 15;
        $query = Invoice::with(['productInvoices.product', 'user'])
            ->whereIn('pay_status', [0, 1, 2]);

        if (isset($value)) {
            $query->where('invoice_type', $value);
        }

        $invoices = $query->paginate($perPage);

        return response()->json($invoices);
    }

    public static function filterByTypeAndStatus(Request $request)
    {
        $invoiceType = $request->input("invoiceType");
        $payStatus = $request->input("payStatus");

        $perPage = 15;
        $query = Invoice::query(); // Bắt đầu với query

        if (!is_null($invoiceType)) {
            $query->where('invoice_type', $invoiceType);
        }

        if (!is_null($payStatus)) {
            if ($payStatus == 3) {
                $query->onlyTrashed();
            } else {
                $query->where('pay_status', $payStatus);
            }
        }

        // Thêm các mối quan hệ sau khi áp dụng các điều kiện
        $query->with([
            'productInvoices.product.productImage',
            'user',
            'supplier',
            'customer'
        ]);

        return $query->paginate($perPage);
    }

    public function filter_pay_status($value = null)
    {
        $perPage = 10;
        $query = Invoice::with(['productInvoices.product', 'user'])
            ->whereIn('pay_status', [0, 1, 2]);

        if (isset($value)) {
            $query->where('pay_status', $value);
        }

        $invoices = $query->paginate($perPage);

        return response()->json($invoices);
    }


    public function searchInvoice(Request $request)
    {
        $invoiceId = $request->input('invoice_id'); // Lấy ID hóa đơn từ request

        if($invoiceId == ''){
            $perPage = 15;
            $invoices = Invoice::with([
                'productInvoices.product.productImage',
                'user',
                'supplier',
                'customer'
            ])->paginate($perPage);
            return response()->json($invoices);
        }

        if (!$invoiceId) {
            return response()->json(['message' => 'Mã hóa đơn là bắt buộc'], 400);
        }

        $invoice = Invoice::searchByInvoiceId($invoiceId);

        if (!$invoice) {
            return response()->json(['message' => 'Không tìm thấy hóa đơn'], 404);
        }

        return response()->json($invoice);
    }

    public function getInvoiceDetails(string $id)
    {
        // Tải hóa đơn và các mối quan hệ liên quan của sản phẩm
        $invoice = Invoice::with([
            'productInvoices.product.productImage',
            'user',
            'supplier',
            'customer'
        ])
            ->find($id);

        if (!$invoice) {
            return response()->json(['error' => 'Hóa đơn không tồn tại.'], 404);
        }

        return response()->json($invoice);
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
                return response()->json(['success' => 'Hóa đơn đã được thêm thành công!', 'invoice_id' => $invoice->id]);
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
            'note' => 'nullable',
            'term' => 'nullable',
            'signature_name' => 'required|string|max:255',
            'pay_status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        DB::beginTransaction();
        try {
            $params = $request->except('_token');
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

            DB::commit();
            return response()->json(['success' => 'Hóa đơn đã được cập nhật thành công!', 'invoice_id' => $invoice->id]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            DB::transaction(function () use ($invoice) {
                // Cập nhật trạng thái hóa đơn
                $invoice->pay_status = 3;
                $invoice->save();

                // Xóa hóa đơn
                $invoice->delete();
            });

            return response()->json(['success' => 'Xóa hóa đơn thành công!']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "Hóa đơn không tồn tại"], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
