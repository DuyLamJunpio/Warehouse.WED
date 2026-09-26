<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductInvoice;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\OrderReceivedMail;
use Tests\TestCase;

class OrderStatusMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_status_mail_is_sent_for_confirmed_shipping_and_completed_but_not_packing(): void
    {
        config([
            'services.storefront.url' => 'https://storefront.test',
            'services.storefront.secret' => 'test-secret',
        ]);
        Http::fake([
            'https://storefront.test/api/orders/status-mail' => Http::response(['sent' => true], 200),
        ]);

        $staff = User::factory()->create();
        $customer = Customer::create([
            'customer_name' => 'Khách thử nghiệm',
            'customer_phone' => '0901234567',
            'customer_email' => 'customer@example.test',
            'status' => 1,
        ]);
        $suffix = Str::lower(Str::random(8));
        $product = Product::create([
            'product_name' => 'Sản phẩm test email',
            'slug' => 'san-pham-test-email-' . $suffix,
            'barcode' => 'MAIL-' . Str::upper($suffix),
            'import_price' => 1000,
            'sell_price' => 2000,
            'status' => 1,
            'manage_stock' => false,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'MAIL-' . Str::upper($suffix),
            'quantity' => 0,
        ]);
        $order = Invoice::withoutEvents(fn () => Invoice::create([
            'user_id' => $staff->id,
            'customer_id' => $customer->id,
            'invoice_type' => Invoice::TYPE_ORDER,
            'order_code' => 'MAIL-' . Str::upper(Str::random(8)),
            'total_amount' => 2000,
            'pay_status' => 0,
            'order_status' => Invoice::STATUS_PENDING,
            'payment_method' => 'cod',
            'shipping_name' => $customer->customer_name,
            'shipping_phone' => $customer->customer_phone,
            'shipping_address' => 'Địa chỉ test',
            'signature_name' => $customer->customer_name,
        ]));
        ProductInvoice::create([
            'invoice_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 2000,
        ]);

        foreach ([
            Invoice::STATUS_CONFIRMED,
            Invoice::STATUS_PACKING,
            Invoice::STATUS_SHIPPING,
            Invoice::STATUS_COMPLETED,
        ] as $to) {
            $this->actingAs($staff)
                ->postJson('/order/' . $order->id . '/status', ['order_status' => $to])
                ->assertOk();

            // Observer gửi sau commit và gom qua terminating callback; flush
            // trong test để mô phỏng đúng request đã kết thúc.
            app(\App\Services\OrderStatusMailer::class)->flush();
        }

        Http::assertSentCount(3);
        $statuses = collect(Http::recorded())
            ->map(fn (array $record) => $record[0]->data()['status'])
            ->all();

        $this->assertSame([
            Invoice::STATUS_CONFIRMED,
            Invoice::STATUS_SHIPPING,
            Invoice::STATUS_COMPLETED,
        ], $statuses);

        Http::assertSent(function ($request) use ($order, $customer) {
            return $request->header('X-Warehouse-Secret')[0] === 'test-secret'
                && $request['order_code'] === $order->order_code
                && $request['customer']['email'] === $customer->customer_email
                && $request['items'][0]['quantity'] === 1;
        });
    }

    public function test_successful_bank_transfer_sends_payment_confirmation_without_confirming_the_order(): void
    {
        Mail::fake();

        $staff = User::factory()->create();
        $customer = Customer::create([
            'customer_name' => 'Khách chuyển khoản',
            'customer_phone' => '0907654321',
            'customer_email' => 'bank-customer@example.test',
            'status' => 1,
        ]);
        $order = Invoice::withoutEvents(fn () => Invoice::create([
            'user_id' => $staff->id,
            'customer_id' => $customer->id,
            'invoice_type' => Invoice::TYPE_ORDER,
            'order_code' => 'BANK-' . Str::upper(Str::random(8)),
            'total_amount' => 2000,
            'pay_status' => 0,
            'order_status' => Invoice::STATUS_PENDING,
            'payment_method' => 'bank_transfer',
            'signature_name' => $customer->customer_name,
        ]));

        $order->forceFill(['pay_status' => 1])->save();
        app()->terminate();

        Mail::assertSent(OrderReceivedMail::class, function (OrderReceivedMail $mail) use ($order) {
            return $mail->order->is($order)
                && str_contains($mail->envelope()->subject, 'xác nhận thanh toán');
        });
        $this->assertSame(Invoice::STATUS_PENDING, $order->fresh()->order_status);
    }
}
