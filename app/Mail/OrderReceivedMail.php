<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isBankPaymentConfirmed()
                ? 'RUNGU đã xác nhận thanh toán đơn hàng #' . $this->order->order_code
                : 'RUNGU đã nhận đơn hàng #' . $this->order->order_code,
        );
    }

    private function isBankPaymentConfirmed(): bool
    {
        return $this->order->payment_method === 'bank_transfer'
            && (int) $this->order->pay_status === 1;
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-received');
    }
}
