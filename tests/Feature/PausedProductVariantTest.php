<?php

namespace Tests\Feature;

use App\Http\Controllers\ProductController;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use ReflectionMethod;
use Tests\TestCase;

class PausedProductVariantTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Isolated SQLite schema only; never connect to or alter the shop database.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('product_styles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('image_model_id')->nullable();
            $table->string('name');
            $table->string('name_key');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('image_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_style_id')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('sku')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedBigInteger('price_override')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_pausing_a_legacy_incomplete_variant_preserves_its_fields_and_saves(): void
    {
        DB::table('product_styles')->insert([
            'id' => 1,
            'product_id' => 1,
            'name' => 'Mẫu cũ',
            'name_key' => 'mẫu cũ',
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('product_variants')->insert([
            'id' => 10,
            'product_id' => 1,
            'product_style_id' => 1,
            'color' => '',
            'size' => 'Size trẻ em',
            'sku' => 'OLD-10',
            'quantity' => 5,
            'price_override' => null,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->syncStyles([[
            'id' => 1,
            'name' => 'Mẫu cũ',
            'variants' => [[
                'id' => 10,
                'color' => '',
                'size' => 'Size trẻ em',
                'quantity' => 0,
                'paused' => 1,
            ]],
        ]]);

        $this->assertSame(0, $result);
        $variant = DB::table('product_variants')->where('id', 10)->first();
        $this->assertSame('', $variant->color);
        $this->assertSame('Size trẻ em', $variant->size);
        $this->assertSame(0, $variant->quantity);
    }

    public function test_active_variants_still_require_color_and_size(): void
    {
        DB::table('product_styles')->insert([
            'id' => 1,
            'product_id' => 1,
            'name' => 'Mẫu cũ',
            'name_key' => 'mẫu cũ',
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(ValidationException::class);
        $this->syncStyles([[
            'id' => 1,
            'name' => 'Mẫu cũ',
            'variants' => [[
                'id' => 0,
                'color' => '',
                'size' => '',
                'quantity' => 0,
                'paused' => 0,
            ]],
        ]]);
    }

    public function test_simple_mode_stores_stock_on_one_internal_sku_without_placeholder_values(): void
    {
        DB::table('product_styles')->insert([
            'id' => 1,
            'product_id' => 1,
            'name' => 'Mẫu mặc định',
            'name_key' => 'mẫu mặc định',
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('product_variants')->insert([
            'id' => 10,
            'product_id' => 1,
            'product_style_id' => 1,
            'color' => 'Mặc định',
            'size' => 'Mặc định',
            'sku' => 'OLD-10',
            'quantity' => 2,
            'price_override' => null,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = app(ProductController::class);
        $method = new ReflectionMethod(ProductController::class, 'syncStylesAndVariants');
        $method->setAccessible(true);
        $product = new Product();
        $product->id = 1;
        $product->barcode = 'BASE-1';

        $result = $method->invoke(
            $controller,
            $product,
            Request::create('/', 'POST', [
                'variant_mode' => Product::VARIANT_MODE_SIMPLE,
                'manage_stock' => 1,
                'stock_quantity' => 12,
            ]),
            [],
        );

        $this->assertSame(12, $result);
        $variant = DB::table('product_variants')->where('id', 10)->first();
        $this->assertNull($variant->product_style_id);
        $this->assertNull($variant->color);
        $this->assertNull($variant->size);
        $this->assertSame(12, $variant->quantity);
    }

    private function syncStyles(array $styles): int
    {
        $controller = app(ProductController::class);
        $method = new ReflectionMethod(ProductController::class, 'syncStylesAndVariants');
        $method->setAccessible(true);
        $product = new Product();
        $product->id = 1;

        return $method->invoke($controller, $product, Request::create('/', 'POST'), $styles);
    }
}
