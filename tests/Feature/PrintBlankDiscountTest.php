<?php

namespace Tests\Feature;

use App\Http\Controllers\API\PrintStorefrontController;
use App\Http\Controllers\PrintBlankController;
use App\Models\PrintBlank;
use App\Models\PrintTechnique;
use App\Services\StorefrontNotifier;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PrintBlankDiscountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Chỉ dùng SQLite trong bộ nhớ, không chạy migration trên database shop.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('users', fn (Blueprint $table) => $table->id());
        Schema::create('invoices', fn (Blueprint $table) => $table->id());
        Schema::create('products', fn (Blueprint $table) => $table->id());
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
        });
        foreach ([
            '2026_08_25_000000_create_print_tables.php',
            '2026_08_26_200000_add_category_to_print_blanks.php',
            '2026_09_06_000000_add_price_to_print_techniques.php',
            '2026_09_23_000000_add_sizes_to_print_blank_colors.php',
            '2026_09_25_000000_add_discount_to_print_blanks.php',
        ] as $migration) {
            (require database_path('migrations/' . $migration))->up();
        }
        // Migration đổi vùng in sang vị trí còn chuyển dữ liệu cũ qua bảng settings;
        // ở đây chỉ cần đúng cột nó thêm vào.
        Schema::table('print_blanks', fn (Blueprint $table) => $table->json('positions')->nullable());
        $this->mock(StorefrontNotifier::class)->shouldReceive('markDirty')->andReturnNull();
    }

    private function technique(int $price = 30000): PrintTechnique
    {
        return PrintTechnique::create(['name' => 'Decal', 'slug' => 'decal', 'price' => $price, 'is_active' => true]);
    }

    private function blankPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Áo thun',
            'base_price' => 100000,
            'frame_width_mm' => 520,
            'frame_height_mm' => 700,
            'positions' => ['front', 'back'],
            'moq' => 1,
            'lead_days' => 3,
            'colors' => [['name' => 'Trắng', 'hex' => '#ffffff', 'sizes' => ['M']]],
        ], $overrides);
    }

    private function createBlank(array $overrides = []): PrintBlank
    {
        app(PrintBlankController::class)->store(Request::create('/print/blanks', 'POST', $this->blankPayload($overrides)));

        return PrintBlank::latest('id')->firstOrFail();
    }

    private function catalogueBlank(): array
    {
        return app(PrintStorefrontController::class)->catalogue()->getData(true)['blanks'][0];
    }

    public function test_admin_saves_both_discount_types(): void
    {
        $blank = $this->createBlank(['discount_type' => 'percent', 'discount_value' => 15]);
        $this->assertSame('percent', $blank->discount_type);
        $this->assertSame(15, $blank->discount_value);

        app(PrintBlankController::class)->update(
            Request::create('/', 'POST', $this->blankPayload(['discount_type' => 'amount', 'discount_value' => 20000])),
            $blank,
        );
        $this->assertSame('amount', $blank->fresh()->discount_type);
        $this->assertSame(20000, $blank->fresh()->discount_value);
    }

    public function test_zero_or_missing_type_is_stored_as_no_discount(): void
    {
        $this->assertNull($this->createBlank(['discount_type' => 'percent', 'discount_value' => 0])->discount_type);

        $blank = $this->createBlank(['name' => 'Áo polo', 'discount_type' => null, 'discount_value' => 5000]);
        $this->assertNull($blank->discount_type);
        $this->assertNull($blank->discount_value);
    }

    public function test_percent_above_100_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->createBlank(['discount_type' => 'percent', 'discount_value' => 101]);
    }

    public function test_unknown_discount_type_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->createBlank(['discount_type' => 'free', 'discount_value' => 10]);
    }

    public function test_catalogue_shows_discounted_price_with_compare_price(): void
    {
        $this->createBlank(['discount_type' => 'percent', 'discount_value' => 10]);

        $blank = $this->catalogueBlank();
        $this->assertSame(100000, $blank['base_price']);
        $this->assertSame(90000, $blank['display_price']);
        $this->assertSame(100000, $blank['compare_price']);
        $this->assertSame(['type' => 'percent', 'value' => 10, 'label' => '−10%'], $blank['discount']);
    }

    public function test_catalogue_without_discount_keeps_old_shape(): void
    {
        $this->createBlank();

        $blank = $this->catalogueBlank();
        $this->assertNull($blank['display_price']);
        $this->assertNull($blank['compare_price']);
        $this->assertNull($blank['discount']);
    }

    public function test_server_quote_discounts_blank_plus_print_price(): void
    {
        $technique = $this->technique();
        $blank = $this->createBlank([
            'discount_type' => 'amount',
            'discount_value' => 20000,
            'technique_ids' => [$technique->id],
        ]);

        $text = fn (string $position) => [
            'position' => $position, 'kind' => 'text', 'text_content' => 'Hi',
            'x_mm' => 0, 'y_mm' => 0, 'w_mm' => 50, 'h_mm' => 20,
        ];
        $quote = app(PrintStorefrontController::class)->quote(Request::create('/', 'POST', [
            'blank_id' => $blank->id,
            'technique_id' => $technique->id,
            'color_name' => 'Trắng',
            'size' => 'M',
            'qty' => 2,
            'placements' => [$text('front'), $text('back')],
        ]))->getData(true);

        // 100.000 + 30.000 × 2 mặt − 20.000 = 140.000 mỗi áo.
        $this->assertSame([], $quote['errors']);
        $this->assertSame(140000, $quote['unit_price']);
        $this->assertSame(280000, $quote['total']);
    }
}
