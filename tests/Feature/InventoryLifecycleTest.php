<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductComboItem;
use App\Models\ProductInvoice;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InventoryLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, int $stock, bool $isCombo = false): ProductVariant
    {
        $suffix = Str::lower(Str::random(10));
        $product = Product::create([
            'product_name' => $name,
            'slug' => Str::slug($name) . '-' . $suffix,
            'barcode' => 'TEST-' . Str::upper($suffix),
            'import_price' => 1000,
            'sell_price' => 2000,
            'status' => 1,
            'manage_stock' => true,
            'is_combo' => $isCombo,
        ]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-' . Str::upper($suffix),
            'quantity' => $stock,
        ]);
    }

    private function pendingOrder(ProductVariant $variant, int $quantity): Invoice
    {
        $user = User::factory()->create();
        $order = Invoice::create([
            'user_id' => $user->id,
            'invoice_type' => Invoice::TYPE_ORDER,
            'order_code' => 'TEST-' . Str::upper(Str::random(8)),
            'total_amount' => 10000,
            'pay_status' => 0,
            'order_status' => Invoice::STATUS_PENDING,
            'payment_method' => 'cod',
            'signature_name' => 'Test',
        ]);
        ProductInvoice::create([
            'invoice_id' => $order->id,
            'product_id' => $variant->product_id,
            'variant_id' => $variant->id,
            'quantity' => $quantity,
            'unit_price' => 10000,
        ]);

        return $order;
    }

    public function test_stock_is_deducted_only_when_a_pending_order_is_confirmed_and_restored_on_cancel(): void
    {
        $staff = User::factory()->create();
        $variant = $this->product('Thanh gỗ', 10);
        $order = $this->pendingOrder($variant, 3);

        $this->assertSame(10, (int) $variant->fresh()->quantity);
        $this->assertSame(0, StockMovement::count());

        $this->actingAs($staff)->postJson('/order/' . $order->id . '/status', [
            'order_status' => Invoice::STATUS_CONFIRMED,
        ])->assertOk();

        $this->assertSame(7, (int) $variant->fresh()->quantity);
        $this->assertSame(-3, (int) StockMovement::where('type', StockMovement::TYPE_SALE)->value('quantity_change'));

        $this->actingAs($staff)->postJson('/order/' . $order->id . '/status', [
            'order_status' => Invoice::STATUS_CANCELLED,
        ])->assertOk();

        $this->assertSame(10, (int) $variant->fresh()->quantity);
        $this->assertSame(3, (int) StockMovement::where('type', StockMovement::TYPE_RESTOCK)->value('quantity_change'));
    }

    public function test_confirming_one_combo_deducts_its_component_quantity_and_keeps_invoice_quantity_as_one(): void
    {
        $staff = User::factory()->create();
        $woodBar = $this->product('Thanh gỗ', 20);
        $combo = $this->product('Combo 5 thanh gỗ', 0, true);
        ProductComboItem::create([
            'combo_variant_id' => $combo->id,
            'component_variant_id' => $woodBar->id,
            'quantity' => 5,
        ]);
        $order = $this->pendingOrder($combo, 1);

        $this->actingAs($staff)->postJson('/order/' . $order->id . '/status', [
            'order_status' => Invoice::STATUS_CONFIRMED,
        ])->assertOk();

        $this->assertSame(15, (int) $woodBar->fresh()->quantity);
        $this->assertSame(0, (int) $combo->fresh()->quantity);
        $this->assertSame(1, (int) $order->productInvoices()->value('quantity'));
        $movement = StockMovement::where('invoice_id', $order->id)->where('type', StockMovement::TYPE_SALE)->firstOrFail();
        $this->assertSame($woodBar->id, $movement->variant_id);
        $this->assertSame(-5, (int) $movement->quantity_change);
    }

    public function test_staff_can_save_a_combo_recipe_for_a_product_variant(): void
    {
        $staff = User::factory()->create();
        $woodBar = $this->product('Thanh gỗ', 20);
        $combo = $this->product('Combo thanh gỗ', 0);

        $this->actingAs($staff)->postJson('/product/' . $combo->product_id . '/combo-components', [
            'components' => [[
                'combo_variant_id' => $combo->id,
                'component_variant_id' => $woodBar->id,
                'quantity' => 5,
            ]],
        ])->assertOk();

        $this->assertTrue((bool) $combo->product->fresh()->is_combo);
        $this->assertDatabaseHas('product_combo_items', [
            'combo_variant_id' => $combo->id,
            'component_variant_id' => $woodBar->id,
            'quantity' => 5,
        ]);
    }

    public function test_inventory_history_shows_legacy_sale_without_creating_a_duplicate_movement(): void
    {
        $staff = User::factory()->create();
        $variant = $this->product('Lư gốm màu trắng', 10);
        $order = $this->pendingOrder($variant, 5);

        // Mô phỏng đơn cũ: hệ thống trước sổ cái đã trừ tồn trực tiếp.
        $variant->update(['quantity' => 5]);
        $order->forceFill([
            'order_status' => Invoice::STATUS_COMPLETED,
            'stock_deducted_at' => now()->subMinute(),
        ])->save();

        $this->actingAs($staff)
            ->getJson('/inventory/history/' . $variant->id)
            ->assertOk()
            ->assertJsonFragment([
                'change' => -5,
                'reason' => 'Bán hàng · ' . $order->order_code,
            ]);

        $this->assertSame(0, StockMovement::count());
    }
}
