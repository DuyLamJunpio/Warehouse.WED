<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Báo cho web bán hàng gửi thư khi một đơn đổi trạng thái.
 *
 * Thư do web bán hàng gửi chứ không phải trang này, vì bên đó đã có sẵn tài
 * khoản SMTP và mẫu thư theo nhận diện của shop. Trang này chỉ nói "đơn kia vừa
 * sang trạng thái nọ", kèm đủ dữ liệu để dựng thư mà bên đó không phải hỏi lại.
 *
 * Ba điều cố ý:
 *
 *   1. Gửi sau khi đã trả trang cho nhân viên (`terminating`). Đổi trạng thái là
 *      việc làm nhiều nhất ở trang đơn hàng; không ai phải chờ một chặng mạng và
 *      một máy chủ SMTP mới thấy nút nhả ra.
 *   2. Gom theo từng đơn. Một request có thể chạm nhiều đơn (huỷ hàng loạt đơn
 *      quá hạn), nhưng mỗi đơn chỉ được một lá thư cho lần đổi cuối cùng của nó.
 *   3. Thất bại thì chỉ ghi log. Trạng thái đã đổi xong và đúng; một lá thư không
 *      gửi được không được phép làm thao tác đó trông như đã hỏng.
 */
class OrderStatusMailer
{
    /** Đơn chờ gửi thư, khoá theo id để một đơn chỉ ra một lá. */
    private array $pending = [];

    private bool $scheduled = false;

    private int $timeout = 8;

    public function queue(Invoice $order, ?string $from, string $to): void
    {
        /*
         * Ghi đè bản trước của cùng đơn: chỉ chặng cuối trong request này là thật.
         * Nhưng giữ `from` của lần đầu, để thư nói đúng đơn đã đi từ đâu tới đâu.
         */
        $this->pending[$order->id] = [
            'order' => $order,
            'from' => $this->pending[$order->id]['from'] ?? $from,
            'to' => $to,
        ];

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

        /*
         * Chưa cấu hình thì bỏ qua, nhưng có ghi log: không phải bản cài nào cũng
         * có web bán hàng đi kèm, mà im lặng hoàn toàn thì chủ shop tưởng thư đã
         * gửi rồi.
         */
        if ($url === '' || $secret === '') {
            Log::info('Chưa cấu hình web bán hàng nên không gửi được thư đổi trạng thái đơn.', [
                'orders' => count($queued),
            ]);

            return;
        }

        foreach ($queued as $item) {
            $this->send($url, $secret, $item['order'], $item['from'], $item['to']);
        }
    }

    private function send(string $url, string $secret, Invoice $order, ?string $from, string $to): void
    {
        try {
            $response = Http::withHeaders(['X-Warehouse-Secret' => $secret])
                ->timeout($this->timeout)
                ->post($url.'/api/orders/status-mail', $this->payload($order, $from, $to));

            if ($response->failed()) {
                Log::warning('Web bán hàng từ chối gửi thư đổi trạng thái đơn.', [
                    'order_code' => $order->order_code,
                    'status' => $to,
                    'http' => $response->status(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Không báo được cho web bán hàng gửi thư đổi trạng thái đơn.', [
                'order_code' => $order->order_code,
                'status' => $to,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Đủ dữ liệu để dựng thư trong một lần gọi.
     *
     * Gửi cả giỏ hàng chứ không chỉ mã đơn: web bán hàng không giữ những đơn do
     * nhân viên tự lập tại cửa hàng, mà khách của các đơn đó cũng cần nhận thư
     * giống khách đặt online.
     */
    private function payload(Invoice $order, ?string $from, string $to): array
    {
        $order->loadMissing(['customer', 'productInvoices.product', 'productInvoices.variant']);

        $items = $order->productInvoices->map(fn ($line) => [
            'name' => $line->product?->product_name ?? 'Sản phẩm',
            'variant' => $line->variant?->label ?? '',
            'quantity' => (int) $line->quantity,
            'total' => (int) $line->line_total,
        ])->values()->all();

        $shippingFee = (int) ($order->shipping_fee ?? 0);
        $total = (int) ($order->total_amount ?? 0);

        return [
            'order_code' => $order->order_code,
            'status' => $to,
            'previous_status' => $from,
            // Mili giây, đúng đơn vị web bán hàng dùng cho mọi mốc thời gian.
            'changed_at' => now()->getTimestampMs(),
            'paid' => (int) $order->pay_status === 1,
            'payment_method' => $order->payment_method === 'cod' ? 'cod' : 'banking',
            'customer' => [
                // Tên và số trên đơn là của người nhận, có thể khác hồ sơ khách.
                'name' => $order->shipping_name ?: ($order->customer?->customer_name ?? ''),
                'email' => $order->customer?->customer_email ?? '',
                'phone' => $order->shipping_phone ?: ($order->customer?->customer_phone ?? ''),
                'address' => $order->shipping_address ?? '',
            ],
            'note' => $order->note,
            // Tổng đã gồm phí giao hàng, nên tạm tính là phần trừ ngược ra.
            'subtotal' => max(0, $total - $shippingFee),
            'shipping' => $shippingFee,
            'total' => $total,
            'items' => $items,
        ];
    }
}
