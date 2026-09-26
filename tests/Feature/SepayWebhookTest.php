<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StorefrontPaymentSession;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class SepayWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.sepay.webhook_api_key' => 'test-sepay-key']);
        config(['services.storefront.secret' => 'test-storefront-secret']);
        // Webhook tests không được gọi Telegram thật.
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);
    }

    private function order(string $code, int $amount): Invoice
    {
        return Invoice::create([
            'user_id' => User::factory()->create()->id,
            'invoice_type' => Invoice::TYPE_ORDER,
            'order_code' => $code,
            'total_amount' => $amount,
            'pay_status' => 0,
            'order_status' => Invoice::STATUS_PENDING,
            'payment_method' => 'bank_transfer',
            'signature_name' => 'RUNGU',
        ]);
    }

    private function variant(int $quantity = 5): ProductVariant
    {
        $suffix = Str::lower(Str::random(12));
        $product = Product::create([
            'product_name' => 'Sản phẩm kiểm thử',
            'slug' => 'san-pham-kiem-thu-' . $suffix,
            'barcode' => 'TEST-' . Str::upper($suffix),
            'import_price' => 50000,
            'sell_price' => 100000,
            'status' => 1,
            'manage_stock' => true,
        ]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-' . Str::upper($suffix),
            'quantity' => $quantity,
        ]);
    }

    public function test_bank_checkout_creates_only_a_fifteen_minute_payment_session(): void
    {
        $variant = $this->variant(5);
        $payload = [
            'customer_name' => 'Khách hàng kiểm thử',
            'customer_phone' => '0900000001',
            'customer_email' => 'khach@example.test',
            'province' => 'Hà Nội',
            'ward' => 'Ba Đình',
            'address' => '1 Phan Đình Phùng',
            'payment_method' => 'bank_transfer',
            'expected_total_amount' => 200000,
            'checkout_ref' => (string) Str::uuid(),
            'items' => [['variant_id' => $variant->id, 'quantity' => 2]],
        ];

        $response = $this->postJson('/api/checkout', $payload, [
            'X-Storefront-Secret' => 'test-storefront-secret',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('pay_status', 0)
            ->assertJsonPath('total_amount', 200000)
            ->assertJsonStructure(['checkout_ref', 'order_code', 'payment_reference', 'payment_qr_data_uri', 'expires_at']);
        $this->assertSame(0, Invoice::orders()->count());
        $this->assertSame(1, StorefrontPaymentSession::count());
        $this->assertSame(5, (int) $variant->fresh()->quantity);

        $session = StorefrontPaymentSession::firstOrFail();
        $this->assertSame(StorefrontPaymentSession::STATUS_PENDING, $session->status);
        $this->assertTrue($session->expires_at->between(now()->addMinutes(14), now()->addMinutes(16)));
    }

    public function test_sepay_creates_a_paid_pending_order_and_redeems_voucher_but_not_stock(): void
    {
        $variant = $this->variant(5);
        $voucher = Voucher::create([
            'code' => 'PAYNOW',
            'name' => 'Dùng khi đã thanh toán',
            'type' => Voucher::TYPE_FIXED_AMOUNT,
            'value' => 10000,
            'min_order_amount' => 0,
            'used_count' => 0,
            'status' => true,
        ]);
        $session = StorefrontPaymentSession::create([
            'checkout_ref' => (string) Str::uuid(),
            'checkout_fingerprint' => hash('sha256', 'checkout-session'),
            'payment_code' => 'RUNGU2609254321',
            'total_amount' => 190000,
            'status' => StorefrontPaymentSession::STATUS_PENDING,
            'expires_at' => now()->addMinutes(15),
            'payload' => [
                'customer' => [
                    'customer_name' => 'Khách hàng đã trả tiền',
                    'customer_phone' => '0900000002',
                    'customer_email' => null,
                    'province' => 'Hà Nội',
                    'ward' => 'Ba Đình',
                    'address' => '2 Phan Đình Phùng',
                ],
                'voucher_code' => 'PAYNOW',
                'items' => [[
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'quantity' => 2,
                    'unit_price' => 100000,
                ]],
                'financials' => [
                    'discount' => 10000,
                    'shipping_fee' => 0,
                    'shop_shipping_fee' => 0,
                ],
            ],
        ]);
        $headers = ['Authorization' => 'Apikey test-sepay-key'];
        $payload = [
            'id' => 92708,
            'accountNumber' => '0868238690',
            'transferType' => 'in',
            'transferAmount' => 190000,
            'content' => 'SEVQR RUNGU2609254321',
        ];

        $this->assertSame(0, Invoice::orders()->count());
        $this->assertSame(5, (int) $variant->fresh()->quantity);
        $this->assertSame(0, (int) $voucher->fresh()->used_count);

        $this->postJson('/api/webhooks/sepay', $payload, $headers)->assertOk();

        $order = Invoice::orders()->where('order_code', $session->payment_code)->firstOrFail();
        $this->assertSame(1, (int) $order->pay_status);
        $this->assertSame(Invoice::STATUS_PENDING, $order->order_status);
        $this->assertSame(5, (int) $variant->fresh()->quantity);
        $this->assertSame(1, (int) $voucher->fresh()->used_count);
        $this->assertSame(StorefrontPaymentSession::STATUS_PAID, $session->fresh()->status);

        $this->postJson('/api/webhooks/sepay', $payload, $headers)->assertOk();
        $this->assertSame(1, Invoice::orders()->where('order_code', $session->payment_code)->count());
        $this->assertSame(1, (int) $voucher->fresh()->used_count);
    }

    public function test_expired_vietqr_session_never_creates_an_admin_order(): void
    {
        $session = StorefrontPaymentSession::create([
            'checkout_ref' => (string) Str::uuid(),
            'checkout_fingerprint' => hash('sha256', 'expired-checkout-session'),
            'payment_code' => 'RUNGU2609259876',
            'total_amount' => 120000,
            'status' => StorefrontPaymentSession::STATUS_PENDING,
            'expires_at' => now()->subSecond(),
            'payload' => [],
        ]);

        $this->postJson('/api/webhooks/sepay', [
            'id' => 92709,
            'accountNumber' => '0868238690',
            'transferType' => 'in',
            'transferAmount' => 120000,
            'content' => 'SEVQR RUNGU2609259876',
        ], ['Authorization' => 'Apikey test-sepay-key'])->assertOk();

        $this->assertSame(0, Invoice::orders()->count());
        $this->assertSame(StorefrontPaymentSession::STATUS_EXPIRED, $session->fresh()->status);
    }

    public function test_webhook_requires_key_and_exact_bank_order_and_amount_and_ignores_retries(): void
    {
        $order = $this->order('RUNGU2609241234', 250000);
        $payload = [
            'id' => 92704,
            'accountNumber' => '0868238690',
            'transferType' => 'in',
            'transferAmount' => 250000,
            'content' => 'SEVQR RUNGU2609241234',
        ];

        $this->postJson('/api/webhooks/sepay', $payload)->assertUnauthorized();
        $this->assertSame(0, (int) $order->fresh()->pay_status);

        $headers = ['Authorization' => 'Apikey test-sepay-key'];
        $this->postJson('/api/webhooks/sepay', $payload, $headers)
            ->assertOk()->assertJsonPath('success', true);
        $this->assertSame(1, (int) $order->fresh()->pay_status);
        $this->assertSame(Invoice::STATUS_PENDING, $order->fresh()->order_status);

        $this->postJson('/api/webhooks/sepay', $payload, $headers)
            ->assertOk()->assertJsonPath('success', true);
        $this->assertSame(1, DB::table('sepay_transactions')->count());
    }

    public function test_wrong_amount_or_bank_never_marks_order_paid(): void
    {
        $order = $this->order('RUNGU2609245678', 300000);
        $headers = ['Authorization' => 'Apikey test-sepay-key'];
        $payload = [
            'id' => 92705,
            'accountNumber' => '0868238690',
            'transferType' => 'in',
            'transferAmount' => 299000,
            'code' => 'RUNGU2609245678',
        ];

        $this->postJson('/api/webhooks/sepay', $payload, $headers)->assertOk();
        $this->assertSame(0, (int) $order->fresh()->pay_status);
        $this->assertSame('amount_mismatch', DB::table('sepay_transactions')->value('result'));

        $payload['id'] = 92706;
        $payload['transferAmount'] = 300000;
        $payload['accountNumber'] = '0000000000';
        $this->postJson('/api/webhooks/sepay', $payload, $headers)->assertOk();
        $this->assertSame(0, (int) $order->fresh()->pay_status);
    }

    public function test_voucher_is_counted_only_after_payment_is_confirmed_once(): void
    {
        $voucher = Voucher::create([
            'code' => 'PAYMENT10',
            'name' => 'Giảm giá khi thanh toán',
            'type' => Voucher::TYPE_FIXED_AMOUNT,
            'value' => 10000,
            'min_order_amount' => 0,
            'used_count' => 0,
            'status' => true,
        ]);
        $order = $this->order('RUNGU2609249012', 250000);
        $order->note = 'Voucher: PAYMENT10';
        $order->save();
        $headers = ['Authorization' => 'Apikey test-sepay-key'];
        $payload = [
            'id' => 92707,
            'accountNumber' => '0868238690',
            'transferType' => 'in',
            'transferAmount' => 250000,
            'code' => 'RUNGU2609249012',
        ];

        $this->assertSame(0, (int) $voucher->fresh()->used_count);
        $this->postJson('/api/webhooks/sepay', $payload, $headers)->assertOk();
        $this->assertSame(1, (int) $voucher->fresh()->used_count);

        $this->postJson('/api/webhooks/sepay', $payload, $headers)->assertOk();
        $this->assertSame(1, (int) $voucher->fresh()->used_count);
    }
}
