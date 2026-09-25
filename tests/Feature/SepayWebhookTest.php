<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SepayWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.sepay.webhook_api_key' => 'test-sepay-key']);
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
