<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gửi thông báo vận hành tới nhóm Telegram của cửa hàng.
 *
 * Telegram chỉ được gọi sau khi nghiệp vụ đã commit và mọi lỗi mạng đều được
 * ghi log thay vì làm hỏng việc tạo đơn hoặc đối soát SePay.
 */
class TelegramNotifier
{
    public function orderCreated(Invoice $invoice): void
    {
        $invoice->loadMissing(['customer', 'productInvoices.product', 'productInvoices.variant']);

        $items = $invoice->productInvoices
            ->map(function ($line) {
                $name = $line->product?->product_name ?? 'Sản phẩm đã xóa';
                $variant = $line->variant?->label;
                $label = $variant ? " ({$variant})" : '';

                return sprintf(
                    '• %s%s × %d = %s',
                    $this->escape($name),
                    $this->escape($label),
                    (int) $line->quantity,
                    $this->money((int) $line->line_total),
                );
            })
            ->implode("\n");

        if ($items === '') {
            $items = '• Đơn không có dòng sản phẩm bán sẵn';
        }

        $customer = $invoice->shipping_name
            ?: $invoice->customer?->customer_name
            ?: 'Khách vãng lai';

        $message = implode("\n", [
            '<b>🛒 ĐƠN HÀNG MỚI</b>',
            'Mã đơn: <code>' . $this->escape((string) $invoice->order_code) . '</code>',
            'Khách: ' . $this->escape((string) $customer),
            'SĐT: ' . $this->escape((string) ($invoice->shipping_phone ?? $invoice->customer?->customer_phone ?? '—')),
            'Thanh toán: ' . $this->escape($this->paymentLabel($invoice->payment_method)),
            'Trạng thái: ' . $this->escape($invoice->order_status_label ?? (string) $invoice->order_status),
            '',
            '<b>Sản phẩm</b>',
            $items,
            '',
            '<b>Tổng đơn: ' . $this->money((int) $invoice->total_amount) . '</b>',
        ]);

        $this->send($message);
    }

    /**
     * Báo một giao dịch SePay đã khớp với đơn.
     * $transaction có thể chứa accountNumber, transferType và content từ SePay.
     */
    public function paymentMatched(string $orderCode, int $amount, array $transaction = []): void
    {
        $direction = ($transaction['transferType'] ?? 'in') === 'out'
            ? 'Tiền ra'
            : 'Tiền vào';

        $lines = [
            '<b>✅ BIẾN ĐỘNG SỐ DƯ — ĐÃ KHỚP ĐƠN</b>',
            'Mã đơn: <code>' . $this->escape($orderCode ?: 'Không xác định') . '</code>',
            'Số tiền: <b>' . $this->money($amount) . '</b>',
            'Loại: ' . $this->escape($direction),
            'Tài khoản: ' . $this->escape((string) ($transaction['accountNumber'] ?? '—')),
            'Nội dung: ' . $this->escape((string) ($transaction['content'] ?? '—')),
            'Mã giao dịch SePay: <code>' . $this->escape((string) ($transaction['id'] ?? '—')) . '</code>',
        ];

        if (isset($transaction['accumulated']) && $transaction['accumulated'] !== '') {
            $lines[] = 'Số dư sau giao dịch: <b>' . $this->money((int) $transaction['accumulated']) . '</b>';
        }

        $message = implode("\n", $lines);

        $this->send($message);
    }

    /** Báo giao dịch cần nhân viên đối soát. */
    public function paymentIssue(
        string $result,
        string $orderCode,
        int $amount,
        int $transactionId,
        array $transaction = [],
    ): void {
        $lines = [
            '<b>⚠️ BIẾN ĐỘNG SỐ DƯ — CẦN ĐỐI SOÁT</b>',
            'Kết quả: <code>' . $this->escape($result) . '</code>',
            'Mã đơn: <code>' . $this->escape($orderCode ?: 'Không xác định') . '</code>',
            'Số tiền: <b>' . $this->money($amount) . '</b>',
            'Loại: ' . $this->escape((string) ($transaction['transferType'] ?? '—')),
            'Tài khoản: ' . $this->escape((string) ($transaction['accountNumber'] ?? '—')),
            'Nội dung: ' . $this->escape((string) ($transaction['content'] ?? '—')),
            'Mã giao dịch SePay: <code>' . $this->escape((string) ($transaction['id'] ?? $transactionId)) . '</code>',
        ];

        if (isset($transaction['accumulated']) && $transaction['accumulated'] !== '') {
            $lines[] = 'Số dư sau giao dịch: <b>' . $this->money((int) $transaction['accumulated']) . '</b>';
        }

        $message = implode("\n", $lines);

        $this->send($message);
    }

    private function send(string $message): void
    {
        $token = trim((string) config('services.telegram.bot_token'));
        $chatId = trim((string) config('services.telegram.chat_id'));

        if ($token === '' || $chatId === '') {
            Log::warning('Bỏ qua thông báo Telegram vì chưa đủ TELEGRAM_BOT_TOKEN/TELEGRAM_CHAT_ID.');
            return;
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        $topicId = trim((string) config('services.telegram.topic_id'));
        if ($topicId !== '') {
            $payload['message_thread_id'] = (int) $topicId;
        }

        try {
            $response = Http::asJson()
                ->timeout(8)
                ->post('https://api.telegram.org/bot' . $token . '/sendMessage', $payload);

            if ($response->failed() || $response->json('ok') !== true) {
                Log::warning('Telegram từ chối thông báo QLBH.', [
                    'status' => $response->status(),
                    'description' => $response->json('description'),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Không gửi được thông báo Telegram QLBH.', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function money(int $amount): string
    {
        return number_format($amount, 0, ',', '.') . ' đ';
    }

    private function paymentLabel(?string $method): string
    {
        return match ($method) {
            'bank_transfer', 'banking' => 'Chuyển khoản',
            'cod' => 'COD',
            'cash' => 'Tiền mặt',
            'card' => 'Quẹt thẻ',
            default => $method ?: 'Chưa chọn',
        };
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
