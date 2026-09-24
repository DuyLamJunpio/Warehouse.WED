<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));
        $filter = $request->input('filter', 'all');

        $query = Voucher::query()->latest('id');

        if ($keyword !== '') {
            $query->where(function ($builder) use ($keyword) {
                $builder->where('code', 'ilike', "%{$keyword}%")
                    ->orWhere('name', 'ilike', "%{$keyword}%");
            });
        }

        if ($filter === 'active') {
            $query->where('status', true)
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'));
        } elseif ($filter === 'inactive') {
            $query->where('status', false);
        } elseif ($filter === 'expired') {
            $query->whereNotNull('ends_at')->where('ends_at', '<', now());
        }

        return view('voucher.index', [
            'vouchers' => $query->paginate(self::PER_PAGE)->withQueryString(),
            'types' => Voucher::TYPES,
            'filter' => $filter,
            'counts' => [
                'all' => Voucher::count(),
                'active' => Voucher::where('status', true)
                    ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                    ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                    ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
                    ->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        Voucher::create($this->validated($request));

        return redirect()->route('vouchers.index')->with('success', 'Đã tạo voucher mới.');
    }

    public function update(Request $request, Voucher $voucher)
    {
        $voucher->update($this->validated($request, $voucher));

        return redirect()->route('vouchers.index')->with('success', 'Đã cập nhật voucher.');
    }

    /**
     * Thay cho xoá: chỉ tắt voucher để mã và lịch sử sử dụng luôn được bảo toàn.
     */
    public function deactivate(Voucher $voucher)
    {
        $voucher->update(['status' => false]);

        return redirect()->route('vouchers.index')->with('success', "Đã ngừng áp dụng mã {$voucher->code}.");
    }

    public function toggle(Voucher $voucher)
    {
        $voucher->update(['status' => ! $voucher->status]);

        return redirect()->route('vouchers.index')->with('success', $voucher->status ? 'Đã bật voucher.' : 'Đã tắt voucher.');
    }

    private function validated(Request $request, ?Voucher $voucher = null): array
    {
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code'))),
            'value' => $request->input('value') === '' ? 0 : $request->input('value'),
            'min_order_amount' => $request->input('min_order_amount') === '' ? 0 : $request->input('min_order_amount'),
            'max_discount' => $request->input('max_discount') === '' ? null : $request->input('max_discount'),
            'usage_limit' => $request->input('usage_limit') === '' ? null : $request->input('usage_limit'),
        ]);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('vouchers', 'code')->ignore($voucher)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Voucher::TYPES))],
            'value' => ['required', 'integer', 'min:0'],
            'max_discount' => ['nullable', 'integer', 'min:1'],
            'min_order_amount' => ['required', 'integer', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'code.regex' => 'Mã voucher chỉ gồm chữ in hoa, số, dấu gạch ngang hoặc gạch dưới.',
            'ends_at.after_or_equal' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
        ]);

        if ($data['type'] === Voucher::TYPE_PERCENTAGE && ($data['value'] < 1 || $data['value'] > 100)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'value' => 'Voucher phần trăm phải từ 1 đến 100%.',
            ]);
        }

        if ($data['type'] === Voucher::TYPE_FIXED_AMOUNT && $data['value'] < 1) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'value' => 'Voucher giảm tiền phải lớn hơn 0.',
            ]);
        }

        if ($voucher && $data['usage_limit'] !== null && $data['usage_limit'] < $voucher->used_count) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'usage_limit' => 'Giới hạn lượt dùng không thể thấp hơn số lượt đã dùng (' . $voucher->used_count . ').',
            ]);
        }

        // Chỉ voucher phần trăm mới dùng trần giảm. Miễn phí vận chuyển không
        // dùng giá trị giảm, còn voucher giảm tiền không cần trần giảm.
        if ($data['type'] !== Voucher::TYPE_PERCENTAGE) {
            $data['max_discount'] = null;
        }

        if ($data['type'] === Voucher::TYPE_FREE_SHIPPING) {
            $data['value'] = 0;
        }

        $data['status'] = $request->boolean('status');
        return $data;
    }
}
