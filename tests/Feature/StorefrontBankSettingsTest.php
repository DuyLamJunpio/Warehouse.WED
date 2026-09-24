<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontBankSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.storefront.url' => '']);
    }

    public function test_sales_settings_expose_the_configured_bank_recipient(): void
    {
        $this->getJson('/api/storefront/content')
            ->assertOk()
            ->assertJsonPath('sales.bank_transfer.bank.account_number', '0868238690');

        $response = $this->actingAs(User::factory()->create(['role' => 1]))
            ->postJson(route('settings.sales.save'), [
                'sales' => [
                    'bank_transfer' => [
                        'enabled' => 1,
                        'free_shipping' => 1,
                        'shipping_fee' => 0,
                        'fee_payer' => Setting::PAYER_CUSTOMER,
                        'bank' => [
                            'code' => 'VCB',
                            'account_number' => '1234567890',
                            'account_name' => 'RUNGU SHOP',
                        ],
                    ],
                    'cod' => [
                        'enabled' => 1,
                        'free_shipping' => 1,
                        'shipping_fee' => 0,
                        'fee_payer' => Setting::PAYER_CUSTOMER,
                    ],
                ],
            ]);

        $response->assertOk();
        $this->getJson('/api/storefront/content')
            ->assertOk()
            ->assertJsonPath('sales.bank_transfer.bank.code', 'VCB')
            ->assertJsonPath('sales.bank_transfer.bank.account_number', '1234567890')
            ->assertJsonPath('sales.bank_transfer.bank.account_name', 'RUNGU SHOP');
    }
}
