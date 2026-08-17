<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private const PER_PAGE = 15;

    public function index(Request $request)
    {
        return view('customer.index', $this->listData($request));
    }

    public function getData(Request $request)
    {
        return view('customer.data', $this->listData($request));
    }

    /**
     * Danh sách khách kèm số đơn và tổng chi tiêu.
     * Chỉ tính đơn đã hoàn thành: đơn đang giao còn có thể bị hoàn.
     */
    private function listData(Request $request): array
    {
        $keyword = trim((string) $request->input('keyword'));
        $tier = $request->input('tier');

        $query = Customer::query()
            ->withCount(['orders as order_count'])
            ->withSum([
                'orders as total_spent_sum' => fn($q) => $q->where('order_status', Invoice::STATUS_COMPLETED),
            ], 'total_amount')
            ->orderByDesc('total_spent_sum');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_phone', 'like', "%{$keyword}%")
                    ->orWhere('customer_email', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%")
                    ->orWhere('province', 'like', "%{$keyword}%");
            });
        }

        return [
            'customers' => $query->paginate(self::PER_PAGE)->withQueryString(),
            'tierFilter' => $tier,
        ];
    }

    /**
     * Hồ sơ khách: thông tin giao hàng + lịch sử mua.
     */
    public function show(string $id)
    {
        $customer = Customer::with(['orders' => fn($q) => $q->limit(50)])->findOrFail($id);

        return response()->json([
            'id' => $customer->id,
            'customer_name' => $customer->customer_name,
            'customer_phone' => $customer->customer_phone,
            'customer_email' => $customer->customer_email,
            'full_address' => $customer->full_address,
            'note' => $customer->note,
            'tier' => $customer->tier,
            'total_spent' => $customer->total_spent,
            'order_count' => $customer->orders()->count(),
            'orders' => $customer->orders->map(fn($o) => [
                'order_code' => $o->order_code ?? '#' . $o->id,
                'created_at' => $o->created_at->format('d/m/Y H:i'),
                'status' => $o->order_status_label ?? 'Chưa có trạng thái',
                'total_amount' => (int) $o->total_amount,
            ]),
        ]);
    }

    /**
     * Ghi chú chăm sóc khách hàng.
     */
    public function updateNote(Request $request, string $id)
    {
        $data = $request->validate(['note' => 'nullable|string|max:2000']);

        $customer = Customer::findOrFail($id);
        $customer->note = $data['note'] ?? null;
        $customer->save();

        return response()->json(['success' => 'Đã lưu ghi chú.']);
    }


    public function store(Request $request)
    {
        if ($request->isMethod('post') || $request->ajax() || $request->wantsJson()) {
            // Khách không có tài khoản: số điện thoại là thứ bắt buộc và duy nhất,
            // email có thể bỏ trống vì nhiều khách đặt qua điện thoại.
            $validator = Validator::make($request->all(), [
                'customer_name' => 'required|max:255',
                'customer_phone' => ['required', 'numeric', 'digits_between:9,11', 'unique:' . Customer::class],
                'customer_email' => ['nullable', 'string', 'email', 'max:255'],
                'address' => 'nullable|max:500',
                'province' => 'nullable|max:255',
                'ward' => 'nullable|max:255',
                'note' => 'nullable|string|max:2000',
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

    /**
     * Không cache: dữ liệu khách đổi liên tục khi có đơn mới về,
     * cache theo từ khóa sẽ trả về tổng chi tiêu đã cũ.
     */
    public function search(Request $request)
    {
        return view('customer.data', $this->listData($request));
    }

    public function getCustomerId(string $id)
    {
        $getCustomer = Customer::find($id);
        return response()->json($getCustomer);
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

        if ($request->ajax() || $request->wantsJson()) {
            $validator = Validator::make($request->all(), [
                'customer_name' => 'required|max:255',
                'customer_phone' => [
                    'required',
                    'numeric',
                    'digits_between:9,11',
                    Rule::unique('customers')->ignore($id),
                ],
                'customer_email' => ['nullable', 'string', 'email', 'max:255'],
                'address' => 'nullable|max:500',
                'province' => 'nullable|max:255',
                'ward' => 'nullable|max:255',
                'note' => 'nullable|string|max:2000',
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
