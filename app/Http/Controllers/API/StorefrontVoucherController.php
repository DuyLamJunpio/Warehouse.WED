<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

/** Voucher endpoints consumed server-to-server by the storefront. */
class StorefrontVoucherController extends Controller
{
    public function index()
    {
        $now = now();
        $vouchers = Voucher::query()
            ->where('status', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
            ->latest('id')
            ->limit(12)
            ->get()
            ->map(fn (Voucher $voucher) => $this->payload($voucher))
            ->values();

        return response()->json(['vouchers' => $vouchers]);
    }

    public function validateVoucher(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/'],
            'subtotal' => ['required', 'integer', 'min:0'],
            'shipping' => ['required', 'integer', 'min:0'],
        ]);

        $code = strtoupper(trim($data['code']));
        $voucher = Voucher::where('code', $code)->first();
        if (! $voucher) return response()->json(['error' => 'Mã giảm giá không hợp lệ.'], 422);

        $quote = $voucher->quote((int) $data['subtotal'], (int) $data['shipping']);
        if (isset($quote['error'])) return response()->json(['error' => $quote['error']], 422);

        return response()->json([
            'voucher' => $this->payload($voucher),
            'discount' => $quote['discount'],
            'new_shipping' => $quote['shipping'],
            'message' => "Đã áp dụng mã {$voucher->code}: " . ($voucher->description ?: $voucher->name),
        ]);
    }

    private function payload(Voucher $voucher): array
    {
        return [
            'code' => $voucher->code,
            'name' => $voucher->name,
            'description' => $voucher->description ?: $voucher->name,
            'type' => $voucher->type,
            'value' => (int) $voucher->value,
            'minOrder' => (int) $voucher->min_order_amount,
            'maxDiscount' => $voucher->max_discount === null ? null : (int) $voucher->max_discount,
        ];
    }
}
