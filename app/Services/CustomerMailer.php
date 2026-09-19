<?php

namespace App\Services;

use App\Mail\OrderReceivedMail;
use App\Mail\PromotionMail;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** Gửi thư trực tiếp từ kho tới khách, không làm hỏng thao tác bán hàng nếu SMTP lỗi. */
class CustomerMailer
{
    private array $orderIds = [];

    private array $promotions = [];

    private bool $scheduled = false;

    public function queueOrderReceived(Invoice $order): void
    {
        if ((int) $order->invoice_type !== Invoice::TYPE_ORDER) {
            return;
        }

        $this->orderIds[$order->id] = true;
        $this->schedule();
    }

    /** Lên lịch gửi một chương trình tới mỗi địa chỉ email hợp lệ, không gửi lộ danh sách. */
    public function queuePromotion(string $subject, string $body): int
    {
        $emails = Customer::query()
            ->whereNotNull('customer_email')
            ->pluck('customer_email')
            ->map(fn ($email) => mb_strtolower(trim((string) $email)))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();

        if ($emails === []) {
            return 0;
        }

        $this->promotions[] = [
            'subject' => $subject,
            'body' => $body,
            'emails' => $emails,
        ];
        $this->schedule();

        return count($emails);
    }

    private function schedule(): void
    {
        if ($this->scheduled) {
            return;
        }

        $this->scheduled = true;
        app()->terminating(fn () => $this->flush());
    }

    private function flush(): void
    {
        $orderIds = array_keys($this->orderIds);
        $promotions = $this->promotions;
        $this->orderIds = [];
        $this->promotions = [];
        $this->scheduled = false;

        foreach ($orderIds as $orderId) {
            $order = Invoice::with(['customer', 'productInvoices.product', 'productInvoices.variant'])
                ->find($orderId);

            if (!$order || !$order->customer?->customer_email) {
                continue;
            }

            $this->sendOrder($order);
        }

        foreach ($promotions as $promotion) {
            foreach ($promotion['emails'] as $email) {
                $this->sendPromotion($email, $promotion['subject'], $promotion['body']);
            }
        }
    }

    private function sendOrder(Invoice $order): void
    {
        try {
            Mail::to($order->customer->customer_email)->send(new OrderReceivedMail($order));
        } catch (\Throwable $e) {
            Log::warning('Không gửi được email xác nhận đơn hàng cho khách.', [
                'order_code' => $order->order_code,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function sendPromotion(string $email, string $subject, string $body): void
    {
        try {
            Mail::to($email)->send(new PromotionMail($subject, $body));
        } catch (\Throwable $e) {
            Log::warning('Không gửi được email khuyến mại cho khách.', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
