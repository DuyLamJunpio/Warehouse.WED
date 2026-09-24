<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
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
        $order = $this->order('DH260924AB12', 250000);
        $payload = [
            'id' => 92704,
            'accountNumber' => '0868238690',
            'transferType' => 'in',
            'transferAmount' => 250000,
            'content' => 'Thanh toan DH260924AB12',
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
        $order = $this->order('DH260924XY99', 300000);
        $headers = ['Authorization' => 'Apikey test-sepay-key'];
        $payload = [
            'id' => 92705,
            'accountNumber' => '0868238690',
            'transferType' => 'in',
            'transferAmount' => 299000,
            'code' => 'DH260924XY99',
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
}
