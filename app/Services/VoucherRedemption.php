<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Voucher;
use Illuminate\Support\Facades\Log;

/** Records a voucher only when its order has actually been paid. */
class VoucherRedemption
{
    public function recordPaidOrder(Invoice $order): void
    {
        $code = $this->codeFromOrder($order);

        if ($code === null) {
            return;
        }

        // This runs inside the caller's payment transaction. The order row is
        // already locked, so duplicate SePay deliveries cannot increment twice.
        $voucher = Voucher::where('code', $code)->lockForUpdate()->first();

        if (! $voucher) {
            Log::warning('Không tìm thấy voucher của đơn đã thanh toán.', [
                'order_code' => $order->order_code,
                'voucher_code' => $code,
            ]);

            return;
        }

        $voucher->increment('used_count');
    }

    private function codeFromOrder(Invoice $order): ?string
    {
        if (! preg_match('/(?:^|\R)Voucher:\s*([A-Z0-9_-]{1,50})(?:\R|$)/i', (string) $order->note, $matches)) {
            return null;
        }

        return strtoupper($matches[1]);
    }
}
