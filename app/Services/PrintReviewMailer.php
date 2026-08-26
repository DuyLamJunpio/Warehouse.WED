<?php

namespace App\Services;

use App\Models\PrintDesign;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Báo cho web bán hàng gửi thư khi một mẫu in được duyệt hoặc bị từ chối.
 *
 * Cùng khuôn với OrderStatusMailer và vì cùng một lý do: tài khoản SMTP và mẫu
 * thư theo nhận diện shop đều nằm bên web bán hàng. Trang này chỉ nói "mẫu kia
 * vừa được quyết thế nào", kèm đủ dữ liệu để dựng thư mà bên đó không phải hỏi lại.
 *
 * Đây là lá thư quan trọng nhất của cả module: một mẫu bị từ chối nghĩa là khách
 * vừa mất tiền và chưa biết vì sao. Im lặng ở đúng chỗ này là khách phải tự gọi
 * điện lên hỏi.
 *
 * Gửi sau khi đã trả trang cho nhân viên, và thất bại thì chỉ ghi log — quyết
 * định duyệt đã lưu xong và đúng; một lá thư không gửi được không được phép làm
 * thao tác đó trông như đã hỏng.
 */
class PrintReviewMailer
{
    private array $pending = [];

    private bool $scheduled = false;

    private int $timeout = 8;

    public function queue(PrintDesign $design): void
    {
        $this->pending[$design->id] = $design;

        if ($this->scheduled) {
            return;
        }

        $this->scheduled = true;
        app()->terminating(fn () => $this->flush());
    }

    public function flush(): void
    {
        $queued = $this->pending;
        $this->pending = [];
        $this->scheduled = false;

        if ($queued === []) {
            return;
        }

        $url = rtrim((string) config('services.storefront.url'), '/');
        $secret = (string) config('services.storefront.secret');

        if ($url === '' || $secret === '') {
            Log::info('Chưa cấu hình web bán hàng nên không gửi được thư duyệt thiết kế.', [
                'designs' => count($queued),
            ]);

            return;
        }

        foreach ($queued as $design) {
            $this->send($url, $secret, $design);
        }
    }

    private function send(string $url, string $secret, PrintDesign $design): void
    {
        $payload = $this->payload($design);

        // Không có địa chỉ thì không có gì để gửi. Mẫu chưa vào đơn nào thì cũng
        // chưa có khách để báo — khách còn đang thiết kế.
        if ($payload === null) {
            return;
        }

        try {
            $response = Http::withHeaders(['X-Warehouse-Secret' => $secret])
                ->timeout($this->timeout)
                ->post($url.'/api/print/review-mail', $payload);

            if ($response->failed()) {
                Log::warning('Web bán hàng từ chối gửi thư duyệt thiết kế.', [
                    'design' => $design->code,
                    'http' => $response->status(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Không báo được cho web bán hàng gửi thư duyệt thiết kế.', [
                'design' => $design->code,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /** Đủ dữ liệu để dựng thư trong một lần gọi; null khi chưa có ai để gửi. */
    private function payload(PrintDesign $design): ?array
    {
        $design->loadMissing(['blank', 'technique', 'invoice.customer']);

        $email = $design->invoice?->customer?->customer_email;
        if (!$email) {
            return null;
        }

        return [
            'design_code' => $design->code,
            'decision' => $design->review_status,
            'note' => $design->review_note,
            'customer_name' => $design->invoice?->shipping_name ?? $design->invoice?->customer?->customer_name,
            'customer_email' => $email,
            'order_code' => $design->invoice?->order_code,
            'blank_name' => $design->blank?->name,
            'technique_name' => $design->technique?->name,
            'color_name' => $design->color_name,
            'size' => $design->size,
            'qty' => $design->qty,
            'total_price' => $design->total_price,
            // Đơn đã thu tiền thì thư từ chối phải nói tới việc hoàn tiền, và
            // nói vào đúng tài khoản khách đã để lại.
            'paid' => (int) ($design->invoice?->pay_status ?? 0) === 1,
            'refund_bank_name' => $design->invoice?->refund_bank_name,
            'refund_account_number' => $design->invoice?->refund_account_number,
            'refund_account_name' => $design->invoice?->refund_account_name,
        ];
    }
}
