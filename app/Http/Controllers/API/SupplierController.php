<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $supplier = Supplier::with([
            'invoices' => function ($query) {
                $query->where('invoice_type', 0);
            }
        ])->paginate(15);

        // Lấy mảng các nhà cung cấp từ đối tượng phân trang
        $items = $supplier->items();

        // Biến đổi mỗi nhà cung cấp để thêm tổng số tiền hóa đơn
        foreach ($items as $item) {
            $totalAmount = $item->invoices->sum('total_amount');
            $item->total_amount = $totalAmount;
        }
        return response()->json($supplier);
    }

    public function getSupplier(string $id)
    {
        $supplier = Supplier::with([
            'invoices' => function ($query) {
                $query->where('invoice_type', 0);
            }
        ])->find($id);

        if (!$supplier) {
            return response()->json(['error' => 'Nhà cung cấp không tồn tại.'], 404);
        }

        // Tính tổng số tiền hóa đơn
        $totalAmount = $supplier->invoices->sum('total_amount');
        $supplier->total_amount = $totalAmount;

        return response()->json($supplier);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword'); // Lấy từ khóa từ request, keyword = supplier_name hoặc supplier_phone hoặc address
        if (!empty($keyword)) {
            $key = "search_{$keyword}"; // Tạo một khóa cache duy nhất dựa trên từ khóa
            $suppliers = Cache::remember($key, 60 * 60, function () use ($keyword) {
                return Supplier::where('supplier_name', 'like', "%{$keyword}%")
                    ->orWhere('supplier_phone', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%")
                    ->with('invoices')
                    ->get();
            });

            // Biến đổi mỗi nhà cung cấp để thêm tổng số tiền hóa đơn
            foreach ($suppliers as $supplier) {
                $totalAmount = $supplier->invoices->sum('total_amount');
                $supplier->total_amount = $totalAmount;
            }
        } else {
            $perPage = 15;
            $suppliers = Supplier::with('invoices')->paginate($perPage);

            // Biến đổi mỗi nhà cung cấp để thêm tổng số tiền hóa đơn
            foreach ($suppliers as $supplier) {
                $totalAmount = $supplier->invoices->sum('total_amount');
                $supplier->total_amount = $totalAmount;
            }
        }
        return response()->json($suppliers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->isMethod("POST")) {
            $validator = Validator::make($request->all(), [
                'supplier_name' => 'required|max:255', // Giới hạn tên nhà cung cấp tối đa 255 ký tự
                'supplier_phone' => ['required', 'numeric', 'digits_between:9,11', 'unique:' . Supplier::class], // Cho phép số điện thoại từ 9 đến 11 chữ số
                'address' => 'required|max:500', // Giới hạn địa chỉ tối đa 500 ký tự
                'tax' => ['required','numeric','digits_between:10,13', 'unique:' . Supplier::class], // Cho phép mã số thuế từ 10 đến 13 chữ số
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }
            $params = $request->except('_token');
            $params['status'] = 1;
            $supplier = Supplier::create($params);
            if ($supplier->id) {
                return response()->json(['success' => 'Nhà cung cấp đã được thêm thành công!']);
            } else {
                return response()->json(['error' => 'Có lỗi xảy ra, vui lòng thử lại.'], 500);
            }
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $supplier = Supplier::find($id);
        if ($request->isMethod('POST')) {
            $validator = Validator::make($request->all(), [
                'supplier_name' => 'required|max:255', // Giới hạn tên nhà cung cấp tối đa 255 ký tự
                'supplier_phone' => ['required', 'numeric', 'digits_between:9,11', 'unique:' . Supplier::class], // Cho phép số điện thoại từ 9 đến 11 chữ số
                'address' => 'required|max:500', // Giới hạn địa chỉ tối đa 500 ký tự
                'tax' => ['required','numeric','digits_between:10,13', 'unique:' . Supplier::class], // Cho phép mã số thuế từ 10 đến 13 chữ số
            ]);
            $params = $request->except('_token');
            $result = $supplier->update($params);
            if ($result) {
                return response()->json(['success' => 'Nhà cung cấp đã được sửa thành công!']);
            } else {
                return response()->json(['error' => 'Có lỗi xảy ra, vui lòng thử lại.'], 500);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Supplier $supplier)
    {
        $this->authorize('delete', $supplier);
        $supplier = Supplier::find($id);
        $supplier->delete();
        return response()->json(['success' => 'Nhà cung cấp đã được xóa thành công!']);
    }
}
