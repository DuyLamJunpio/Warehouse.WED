<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{

    public function index()
    {
        $perPage = 15;
        // Lấy ra danh sách khách hàng với tổng tiền hàng đã mua từ hóa đơn có trạng thái là đã trả và nợ
        $customers = Customer::withCount([
            'invoices as invoice_quantity' => function ($query) {
                $query->where('invoice_type', 1);
            }
        ])->with([
                    'invoicesPaid' => function ($query) {
                        $query->where('pay_status', 1)
                            ->where('invoice_type', 1)
                            ->selectRaw('customer_id, SUM(total_amount) as total_paid')
                            ->groupBy('customer_id'); // Sử dụng partner_id để nhóm
                    },
                    'invoicesOwed' => function ($query) {
                        $query->where('pay_status', 0)
                            ->where('invoice_type', 1)
                            ->selectRaw('customer_id, SUM(total_amount) as total_owed')
                            ->groupBy('customer_id'); // Sử dụng partner_id để nhóm
                    }
                ])->paginate($perPage);

        return response()->json($customers);
    }


    public function store(Request $request)
    {
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'customer_name' => 'required|max:255', // Giới hạn tên nhà cung cấp tối đa 255 ký tự
                'customer_phone' => ['required', 'numeric', 'digits_between:9,11', 'unique:' . Customer::class], // Cho phép số điện thoại từ 9 đến 11 chữ số
                'customer_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . Customer::class],
                'address' => 'required|max:500', // Giới hạn địa chỉ tối đa 500 ký tự
                'avatar' => 'nullable|file|image|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $params = $request->except('_token');
            $params['status'] = 0;
            if ($request->hasFile('avatar')) {
                $image = $request->file('avatar');
                $filename = $image->store('public/images');
                $params['avatar'] = $filename;
            }
            $customer = Customer::create($params);
            if ($customer->id) {
                return response()->json(['success' => 'Khách hàng đã được thêm thành công!']);
            } else {
                return response()->json(['error' => 'Có lỗi xảy ra, vui lòng thử lại.'], 500);
            }
        }
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword'); // Lấy từ khóa từ request, keyword = customer_name,phone,address
        if ($keyword > 0) {
            $key = "search_{$keyword}"; // Tạo một khóa cache duy nhất dựa trên từ khóa
            $Customer = Cache::remember($key, 60 * 60, function () use ($keyword) {
                return Customer::where('Customer_name', 'like', "%{$keyword}%")
                    ->orWhere('Customer_phone', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%")
                    ->get();
            });
        }
        return response()->json($Customer);
    }

    public function getCustomerId(string $id)
    {
        $customer = Customer::where('id', $id)
            ->withCount([
                'invoices as invoice_quantity' => function ($query) {
                    $query->where('invoice_type', 1);
                }
            ])->with([
                'invoicesPaid' => function ($query) {
                    $query->where('pay_status', 1)
                          ->where('invoice_type', 1)
                          ->selectRaw('customer_id, SUM(total_amount) as total_paid')
                          ->groupBy('customer_id');
                },
                'invoicesOwed' => function ($query) {
                    $query->where('pay_status', 0)
                          ->where('invoice_type', 1)
                          ->selectRaw('customer_id, SUM(total_amount) as total_owed')
                          ->groupBy('customer_id');
                }
            ])->firstOrFail(); // Sử dụng firstOrFail để trả về lỗi nếu không tìm thấy khách hàng

        return response()->json($customer);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['error' => 'Khách hàng không tồn tại.'], 404);
        }

        if ($request->isMethod('POST')) {
            $validator = Validator::make($request->all(), [
                'customer_name' => 'required|max:255',
                'customer_phone' => [
                    'required',
                    'numeric',
                    'digits_between:9,11',
                    Rule::unique('customers')->ignore($id),
                ],
                'customer_email' => [
                    'required',
                    'string',
                    'lowercase',
                    'email',
                    'max:255',
                    Rule::unique('customers')->ignore($id),
                ],
                'address' => 'required|max:500',
                'avatar' => 'nullable|file|image|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $params = $request->except('_token');
            if ($request->hasFile('avatar')) {
                if ($customer->avatar && Storage::exists($customer->avatar)) {
                    Storage::delete($customer->avatar);
                }
                $image = $request->file('avatar');
                $filename = $image->store('public/images');
                $params['avatar'] = $filename;
            }

            $result = $customer->update($params);
            if ($result) {
                return response()->json(['success' => 'Khách hàng đã được sửa thành công!']);
            } else {
                return response()->json(['error' => 'Có lỗi xảy ra, vui lòng thử lại.'], 500);
            }
        }
    }


    public function destroy(string $id)
    {
        $Customer = Customer::find($id);
        $Customer->delete();
        return response()->json(['success' => 'Khách hàng đã được xóa thành công!']);
    }
}
