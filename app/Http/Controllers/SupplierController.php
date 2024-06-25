<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use function PHPSTORM_META\map;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $supplier = Supplier::with(['invoices' => function($query) {
            $query->where('invoice_type', 0);
        }])->paginate(10);

        // Lấy mảng các nhà cung cấp từ đối tượng phân trang
        $items = $supplier->items();

        // Biến đổi mỗi nhà cung cấp để thêm tổng số tiền hóa đơn
        foreach ($items as $item) {
            $totalAmount = $item->invoices->sum('total_amount');
            $item->total_amount = $totalAmount;
        }

        return view("supplier.index", compact("supplier"));
    }

    public function getData()
    {
        $supplier = Supplier::with(['invoices' => function($query) {
            $query->where('invoice_type', 0);
        }])->get();

        $supplier->map(function ($supplier) {
            $totalAmount = $supplier->invoices->sum('total_amount');
            $supplier->total_amount = $totalAmount;
            return $supplier;
        });
        return view("supplier.data", compact("supplier"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $request->validate([
                'supplier_name' => 'required|max:255', // Giới hạn tên nhà cung cấp tối đa 255 ký tự
                'supplier_phone' => 'required|numeric|digits_between:9,11', // Cho phép số điện thoại từ 9 đến 11 chữ số
                'address' => 'required|max:500', // Giới hạn địa chỉ tối đa 500 ký tự
                'tax' => 'required|numeric|digits_between:10,13', // Giữ nguyên quy tắc cho mã số thuế
            ]);
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

    public function search(Request $request)
    {
        $keyword = $request->input('keyword'); // Lấy từ khóa từ request
        if ($keyword > 0) {
            $key = "search_{$keyword}"; // Tạo một khóa cache duy nhất dựa trên từ khóa
            $supplier = Cache::remember($key, 60 * 60, function () use ($keyword) {
                return Supplier::where('supplier_name', 'like', "%{$keyword}%")
                    ->orWhere('supplier_phone', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%")
                    ->get();
            });
        } else {
            $perPage = 15;
            $supplier = Supplier::paginate($perPage);
        }
        return view("supplier.data", compact("supplier"));
    }

    public function getSupplierId(string $id)
    {
        $getSupplier = Supplier::find($id);
        return response()->json($getSupplier);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $supplier = Supplier::find($id);
        if ($request->ajax() || $request->wantsJson()) {
            $request->validate([
                'supplier_name' => 'required|max:255', // Giới hạn tên nhà cung cấp tối đa 255 ký tự
                'supplier_phone' => 'required|numeric|digits_between:9,11', // Cho phép số điện thoại từ 9 đến 11 chữ số
                'address' => 'required|max:500', // Giới hạn địa chỉ tối đa 500 ký tự
                'tax' => 'required|numeric|digits_between:10,13', // Giữ nguyên quy tắc cho mã số thuế
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
        $supplier = Supplier::find($id);
        $supplier->delete();
        return response()->json(['success' => 'Nhà cung cấp đã được xóa thành công!']);
    }
}
