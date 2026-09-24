<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Support\ProductPricing;
use PHPUnit\Framework\TestCase;

class ProductPricingTest extends TestCase
{
    public function test_fixed_discount_is_subtracted_from_the_original_price(): void
    {
        $this->assertSame(225000, ProductPricing::discountedPrice(235000, 'amount', 10000));
        $this->assertNull(ProductPricing::discountedPrice(235000, 'amount', 0));
    }

    public function test_percentage_discount_produces_a_sale_price(): void
    {
        $this->assertSame(211500, ProductPricing::discountedPrice(235000, 'percent', 10));
        $this->assertSame(0, ProductPricing::discountedPrice(235000, 'percent', 100));
    }

    public function test_discount_percent_is_derived_from_the_saved_prices(): void
    {
        $product = new Product(['sell_price' => 235000, 'discount_price' => 225000]);
        $this->assertSame(4.3, $product->discount_percent);

        $noDiscount = new Product(['sell_price' => 235000, 'discount_price' => null]);
        $this->assertNull($noDiscount->discount_percent);

        $freeProduct = new Product(['sell_price' => 235000, 'discount_price' => 0]);
        $this->assertSame(100.0, $freeProduct->discount_percent);
    }
}
