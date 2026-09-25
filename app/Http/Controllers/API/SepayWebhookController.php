<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Setting;
use App\Services\TelegramNotifier;
use App\Services\VoucherRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SepayWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $key = (string) config('services.sepay.webhook_api_key');
        if ($key === '') {
            return response()->json(['success' => false], 503);
        }

        $authorization = (string) $request->header('Authorization');
        if (!preg_match('/^Apikey (.+)$/i', $authorization, $matches)
            || !hash_equals($key, $matches[1])) {
            return response()->json(['success' => false], 401);
        }

        $data = $request->validate([
            'id' => 'required|integer|min:1',
            'accountNumber' => 'required|string|max:50',
            'transferType' => 'required|string|in:in,out',
            'transferAmount' => 'required|integer|min:1',
            'accumulated' => 'nullable|integer|min:0',
            'transactionDate' => 'nullable|string|max:50',
            'referenceCode' => 'nullable|string|max:100',
            'code' => 'nullable|string|max:100',
            'content' => 'nullable|string|max:1000',
        ]);

        $paymentPattern = $this->paymentCodePattern();
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        if (! preg_match('/^' . $paymentPattern . '$/', $code)) {
            preg_match('/(?<![A-Z0-9])(' . $paymentPattern . ')(?![A-Z0-9])/i',
                (string) ($data['content'] ?? ''), $match);
            $code = strtoupper($match[1] ?? '');
        }

        try {
            $result = DB::transaction(function () use ($data, $code) {
                $transactionId = (int) $data['id'];
                if (DB::table('sepay_transactions')->where('transaction_id', $transactionId)->exists()) {
                    return [
                        'result' => 'duplicate',
                        'order_code' => $code,
                        'amount' => (int) $data['transferAmount'],
                        'transaction_id' => $transactionId,
                    ];
                }

                $bank = Setting::sales()['bank_transfer']['bank'];
                $accountMatches = hash_equals(
                    preg_replace('/\D+/', '', (string) $bank['account_number']),
                    preg_replace('/\D+/', '', $data['accountNumber']),
                );
                $order = $code !== ''
                    ? Invoice::orders()
                        ->where(fn ($query) => $query
                            ->where('order_code', $code)
                            ->orWhere('legacy_order_code', $code))
                        ->lockForUpdate()
                        ->first()
                    : null;
                // Mã DH cũ chỉ còn là mã đối chiếu cho những giao dịch đang dở dang.
                // Thông báo và lịch sử mới luôn hiển thị mã RUNGU hiện hành.
                $matchedOrderCode = $order?->order_code ?: $code;
                $result = 'unmatched';

                if ($data['transferType'] !== 'in' || !$accountMatches) {
                    $result = 'wrong_direction_or_account';
                } elseif ($order && $order->payment_method !== 'bank_transfer') {
                    $result = 'wrong_payment_method';
                } elseif ($order && (int) $order->total_amount !== (int) $data['transferAmount']) {
                    $result = 'amount_mismatch';
                } elseif ($order && (int) $order->pay_status === 1) {
                    $result = 'already_paid';
                } elseif ($order) {
                    $order->pay_status = 1;
                    $order->payment_expires_at = null;
                    if (in_array($order->order_status, Invoice::STATUS_RESTOCK, true)) {
                        $order->note = trim(($order->note ? $order->note . "\n" : '')
                            . 'CẦN XỬ LÝ: SePay ghi nhận tiền sau khi đơn đã hủy/hoàn.');
                        $result = 'paid_after_cancel';
                    } else {
                        try {
                            $order->deductStockLines();
                            $result = 'paid';
                        } catch (\RuntimeException $e) {
                            // Khách đã trả tiền nhưng hàng đã được bán trong thời
                            // gian chờ chuyển khoản: ghi nhận tiền và báo nhân
                            // viên xử lý, tuyệt đối không trừ kho một phần.
                            $order->note = trim(($order->note ? $order->note . "\n" : '')
                                . 'CẦN XỬ LÝ: đã nhận chuyển khoản nhưng ' . $e->getMessage());
                            $result = 'paid_stock_shortage';
                        }
                    }
                    $order->save();
                    if (in_array($result, ['paid', 'paid_stock_shortage'], true)) {
                        app(VoucherRedemption::class)->recordPaidOrder($order);
                    }
                }

                DB::table('sepay_transactions')->insert([
                    'transaction_id' => $transactionId,
                    'invoice_id' => $order?->id,
                    'order_code' => $matchedOrderCode ?: null,
                    'amount' => (int) $data['transferAmount'],
                    'account_number' => $data['accountNumber'],
                    'result' => $result,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return [
                    'result' => $result,
                    'order_code' => $matchedOrderCode,
                    'amount' => (int) $data['transferAmount'],
                    'transaction_id' => $transactionId,
                ];
            });
        } catch (\Throwable $e) {
            // Unique(transaction_id) cũng bảo vệ khi hai webhook đồng thời vượt qua exists().
            if (DB::table('sepay_transactions')->where('transaction_id', (int) $data['id'])->exists()) {
                return response()->json(['success' => true]);
            }
            Log::error('SePay webhook xử lý thất bại', ['transaction_id' => $data['id'], 'error' => $e->getMessage()]);
            return response()->json(['success' => false], 500);
        }

        if ($result['result'] === 'paid') {
            app(TelegramNotifier::class)->paymentMatched(
                $result['order_code'],
                $result['amount'],
                $data,
            );
        } elseif ($result['result'] !== 'duplicate') {
            app(TelegramNotifier::class)->paymentIssue(
                $result['result'],
                $result['order_code'],
                $result['amount'],
                $result['transaction_id'],
                $data,
            );
            Log::warning('SePay webhook cần đối soát', [
                'transaction_id' => $data['id'], 'order_code' => $result['order_code'], 'result' => $result['result'],
            ]);
        }

        return response()->json(['success' => true]);
    }

    private function paymentCodePattern(): string
    {
        $prefix = strtoupper((string) config('services.sepay.payment_prefix', 'RUNGU'));
        if (! preg_match('/^[A-Z]{2,5}$/', $prefix)) {
            $prefix = 'RUNGU';
        }

        // DH được giữ để khớp các giao dịch đã tạo QR trước khi chuyển sang RUNGU.
        // Các đơn mới dùng PREFIX + 10 chữ số (ngày + chuỗi số ngẫu nhiên).
        return '(?:DH[0-9]{6}[A-Z0-9]{4}|' . preg_quote($prefix, '/') . '[0-9]{3,10})';
    }
}
