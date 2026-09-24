<?php

namespace Tests\Feature;

use App\Models\Categories;
use App\Models\Product;
use Database\Seeders\CanonicalStorefrontCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class CanonicalStorefrontCategoriesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.storefront.url' => '']);
    }

    public function test_seeder_adds_canonical_roots_and_preserves_existing_catalogue(): void
    {
        $other = Categories::create([
            'name' => 'Danh mục khác',
            'slug' => 'danh-muc-khac',
            'sort_order' => 4,
            'status' => 1,
        ]);
        $incense = Categories::create([
            'name' => 'Trầm hương',
            'slug' => 'rungu-demo-tram-huong',
            'sort_order' => 0,
            'status' => 1,
        ]);
        Categories::create([
            'name' => 'Nến hương',
            'slug' => 'rungu-demo-nen-huong',
            'sort_order' => 1,
            'status' => 1,
        ]);
        Categories::create([
            'name' => 'Gỗ thánh',
            'slug' => 'rungu-demo-go-thanh',
            'sort_order' => 2,
            'status' => 1,
        ]);
        $retiredCanonical = Categories::create([
            'name' => 'Tên cũ',
            'slug' => 'huong-thom',
            'image' => 'public/images/old.jpg',
            'description' => 'Giữ mô tả cũ',
            'status' => 0,
        ]);
        $retiredCanonical->delete();
        $product = Product::create([
            'categories_id' => $incense->id,
            'product_name' => 'Trầm thử nghiệm',
            'slug' => 'tram-thu-nghiem',
            'barcode' => 'TRAM-TEST',
            'import_price' => 100,
            'sell_price' => 200,
            'status' => 1,
        ]);

        $seeder = new CanonicalStorefrontCategoriesSeeder();
        $seeder->run();

        $slugs = ['sang-tao', 'huong-thom', 'go-hoa-co', 'dat-va-da', 'phu-kien', 'qua-tang'];
        $this->assertSame($slugs, Categories::roots()->orderBy('sort_order')->limit(6)->pluck('slug')->all());
        $this->assertSame(10, Categories::count());
        $this->assertSame(4, $other->fresh()->sort_order);
        $this->assertSame($incense->id, $product->fresh()->categories_id);
        $this->assertSame(
            Categories::where('slug', 'huong-thom')->value('id'),
            $incense->fresh()->parent_id,
        );
        $this->assertSame(
            Categories::where('slug', 'go-hoa-co')->value('id'),
            Categories::where('slug', 'rungu-demo-go-thanh')->value('parent_id'),
        );
        $this->assertSame($retiredCanonical->id, Categories::where('slug', 'huong-thom')->value('id'));
        $this->assertSame('public/images/old.jpg', $retiredCanonical->fresh()->image);
        $this->assertSame('Giữ mô tả cũ', $retiredCanonical->fresh()->description);
        $this->assertSame(
            'Tác phẩm nghệ nhân và sáng tạo độc bản',
            Categories::where('slug', 'sang-tao')->value('description'),
        );
        $this->assertFalse($retiredCanonical->fresh()->trashed());
        $this->assertSame(1, $retiredCanonical->fresh()->status);
        $this->assertSame([
            'sang-tao',
            'huong-thom',
            'rungu-demo-tram-huong',
            'rungu-demo-nen-huong',
            'go-hoa-co',
            'rungu-demo-go-thanh',
            'dat-va-da',
            'phu-kien',
            'qua-tang',
            'danh-muc-khac',
        ], array_column($this->getJson('/api/storefront/categories')->assertOk()->json('categories'), 'slug'));

        $ids = Categories::whereIn('slug', $slugs)->orderBy('slug')->pluck('id', 'slug')->all();
        $seeder->run();
        $this->assertSame(10, Categories::count());
        $this->assertSame($ids, Categories::whereIn('slug', $slugs)->orderBy('slug')->pluck('id', 'slug')->all());
        $this->assertSame($slugs, Categories::roots()->orderBy('sort_order')->limit(6)->pluck('slug')->all());
    }

    public function test_seeder_stops_if_demo_category_has_children(): void
    {
        $demo = Categories::create([
            'name' => 'Trầm hương',
            'slug' => 'rungu-demo-tram-huong',
        ]);
        Categories::create([
            'parent_id' => $demo->id,
            'name' => 'Nhánh con',
            'slug' => 'nhanh-con',
        ]);

        try {
            (new CanonicalStorefrontCategoriesSeeder())->run();
            $this->fail('Seeder phải dừng khi danh mục demo đã có con.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('rungu-demo-tram-huong', $e->getMessage());
        }

        $this->assertSame(2, Categories::count());
    }

    public function test_public_storefront_product_has_category_identity(): void
    {
        $category = Categories::create([
            'name' => 'Hương thơm',
            'slug' => 'huong-thom',
            'status' => 1,
        ]);
        Product::create([
            'categories_id' => $category->id,
            'product_name' => 'Nến thơm',
            'slug' => 'nen-thom',
            'barcode' => 'NEN-TEST',
            'import_price' => 100,
            'sell_price' => 200,
            'status' => 1,
        ]);

        $this->getJson('/api/storefront/products')
            ->assertOk()
            ->assertJsonPath('products.0.category', 'Hương thơm')
            ->assertJsonPath('products.0.category_id', $category->id)
            ->assertJsonPath('products.0.category_slug', 'huong-thom');
    }
}
