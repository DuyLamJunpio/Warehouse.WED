<?php

namespace Tests\Feature;

use App\Http\Controllers\categoryController;
use App\Http\Controllers\API\StorefrontController;
use App\Models\Categories;
use App\Models\PrintBlank;
use App\Models\Product;
use App\Services\StorefrontNotifier;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CategoryVisibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Chỉ dựng SQLite trong bộ nhớ, tuyệt đối không chạy trên database shop.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('link_url', 2048)->nullable();
            $table->boolean('visibility_override')->nullable();
            $table->integer('sort_order')->default(0);
            $table->integer('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('categories_id')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('print_blanks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('categories_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $this->mock(StorefrontNotifier::class)->shouldReceive('markDirty')->andReturnNull();
    }

    public function test_empty_category_can_be_enabled_manually_and_stays_visible(): void
    {
        $this->insertCategory(1, 'Đồng phục', null, 0, null);

        $response = app(categoryController::class)->edit($this->editRequest([
            'name' => 'Đồng phục',
            'parent_id' => null,
            'description' => null,
            'link_url' => '/in-ao',
            'status' => 1,
        ]), '1');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(1, Categories::findOrFail(1)->status);
        $this->assertTrue(Categories::findOrFail(1)->visibility_override);

        Categories::syncVisibilityStatuses();

        $category = Categories::findOrFail(1);
        $this->assertSame(1, $category->status);
        $this->assertTrue($category->visibility_override);
    }

    public function test_an_enabled_empty_child_makes_its_automatic_parent_visible(): void
    {
        $this->insertCategory(1, 'Áo', null, 0, null);
        $this->insertCategory(2, 'Đồng phục', 1, 0, true);

        Categories::syncVisibilityStatuses();

        $this->assertSame(1, Categories::findOrFail(1)->status);
        $this->assertSame(1, Categories::findOrFail(2)->status);
    }

    public function test_default_category_visibility_still_follows_product_count(): void
    {
        $this->insertCategory(1, 'Áo thun', null, 0, null);

        Categories::syncVisibilityStatuses();
        $this->assertSame(0, Categories::findOrFail(1)->status);

        DB::table('products')->insert([
            'categories_id' => 1,
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Categories::syncVisibilityStatuses();
        $this->assertSame(0, Categories::findOrFail(1)->status);

        DB::table('products')->where('categories_id', 1)->update(['status' => 1]);
        Categories::syncVisibilityStatuses();
        $this->assertSame(1, Categories::findOrFail(1)->status);
    }

    public function test_editing_details_keeps_an_automatic_visibility_rule(): void
    {
        $this->insertCategory(1, 'Áo thun', null, 1, null);
        DB::table('products')->insert([
            'categories_id' => 1,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(categoryController::class)->edit($this->editRequest([
            'name' => 'Áo thun',
            'parent_id' => null,
            'description' => 'Mô tả mới',
            'link_url' => null,
            'status' => 1,
        ]), '1');

        $category = Categories::findOrFail(1);
        $this->assertSame('Mô tả mới', $category->description);
        $this->assertNull($category->visibility_override);
    }

    public function test_changing_a_parent_status_cascades_without_locking_product_child_on(): void
    {
        $this->insertCategory(1, 'Áo', null, 1, true);
        $this->insertCategory(2, 'Áo thun', 1, 1, null);
        DB::table('products')->insert([
            'categories_id' => 2,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = app(categoryController::class);
        $controller->edit($this->editRequest([
            'name' => 'Áo',
            'parent_id' => null,
            'description' => null,
            'link_url' => null,
            'status' => 0,
        ]), '1');

        $this->assertSame(0, Categories::findOrFail(2)->status);
        $this->assertFalse(Categories::findOrFail(2)->visibility_override);

        $controller->edit($this->editRequest([
            'name' => 'Áo',
            'parent_id' => null,
            'description' => null,
            'link_url' => null,
            'status' => 1,
        ]), '1');

        $child = Categories::findOrFail(2);
        $this->assertSame(1, $child->status);
        $this->assertNull($child->visibility_override);
    }

    public function test_storefront_returns_only_categories_marked_as_in_use(): void
    {
        // `status` là nguồn quyết định hiển thị ở trang cửa hàng. Danh mục
        // đang dùng vẫn phải được trả về, kể cả khi chưa có sản phẩm.
        $this->insertCategory(1, 'Danh mục đang dùng', null, 1, null);
        $this->insertCategory(2, 'Danh mục ngưng dùng', null, 0, true);

        $response = app(StorefrontController::class)->categoriesIndex();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([1], collect($response->getData(true)['categories'])->pluck('id')->all());
    }

    public function test_storefront_visibility_hides_products_in_an_inactive_category(): void
    {
        $this->insertCategory(1, 'Danh mục đang dùng', null, 1, null);
        $this->insertCategory(2, 'Danh mục ngưng dùng', null, 0, true);

        DB::table('products')->insert([
            ['id' => 1, 'categories_id' => 1, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'categories_id' => 2, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'categories_id' => 1, 'status' => 0, 'created_at' => now(), 'updated_at' => now()],
            // Hàng hết kho (status = 2) vẫn hiện để khách thấy và đăng ký chờ.
            ['id' => 4, 'categories_id' => 1, 'status' => 2, 'created_at' => now(), 'updated_at' => now()],
            // Sản phẩm legacy chưa xếp danh mục không bị biến mất ngoài ý muốn.
            ['id' => 5, 'categories_id' => null, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $visibleIds = Product::storefrontVisible()->orderBy('id')->pluck('id')->all();

        $this->assertSame([1, 4, 5], $visibleIds);
    }

    public function test_active_print_blanks_are_not_hidden_by_their_category_status(): void
    {
        $this->insertCategory(1, 'Danh mục đang dùng', null, 1, null);
        $this->insertCategory(2, 'Danh mục dùng để nhóm phôi', null, 0, true);

        DB::table('print_blanks')->insert([
            [
                'id' => 1,
                'categories_id' => 2,
                'name' => 'Phôi thuộc danh mục đã tắt',
                'slug' => 'phoi-danh-muc-tat',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'categories_id' => 1,
                'name' => 'Phôi đang tắt riêng',
                'slug' => 'phoi-tat-rieng',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $visibleIds = PrintBlank::storefrontVisible()->orderBy('id')->pluck('id')->all();

        $this->assertSame([1], $visibleIds);
    }

    private function insertCategory(int $id, string $name, ?int $parentId, int $status, ?bool $override): void
    {
        DB::table('categories')->insert([
            'id' => $id,
            'name' => $name,
            'slug' => 'category-' . $id,
            'parent_id' => $parentId,
            'status' => $status,
            'visibility_override' => $override,
            'sort_order' => $id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function editRequest(array $data): Request
    {
        return Request::create('/categories/edit/1', 'POST', $data, [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);
    }
}
