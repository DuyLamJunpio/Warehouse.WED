<?php

namespace Tests\Feature;

use App\Http\Controllers\PrintTechniqueController;
use App\Models\PrintPricingVersion;
use App\Models\PrintTechnique;
use App\Services\PrintPricing;
use App\Services\StorefrontNotifier;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrintFlatPricingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Chỉ dùng SQLite trong bộ nhớ, không chạy migration trên database shop.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('users', fn (Blueprint $table) => $table->id());
        Schema::create('invoices', fn (Blueprint $table) => $table->id());
        (require database_path('migrations/2026_08_25_000000_create_print_tables.php'))->up();
        (require database_path('migrations/2026_09_06_000000_add_price_to_print_techniques.php'))->up();
        $this->mock(StorefrontNotifier::class)->shouldReceive('markDirty')->andReturnNull();
    }

    public function test_create_update_and_toggle_apply_prices_and_preserve_versions(): void
    {
        $controller = app(PrintTechniqueController::class);
        $controller->store(Request::create('/print/techniques', 'POST', ['name' => 'In thử', 'price' => 30000]));
        $technique = PrintTechnique::firstOrFail();
        $version = PrintPricingVersion::latestPublished();
        $this->assertSame(30000, PrintPricing::current()['techniques'][0]['price']);
        $this->assertSame('flat', $version->data['mode']);
        $this->assertSame([], $version->data['rules']);
        $this->assertSame([], $version->data['qty_tiers']);
        $this->assertSame(30000, $version->data['cells'][$technique->id][1]);

        $controller->update(Request::create('/', 'POST', ['name' => 'In thử', 'price' => 45000]), $technique);
        $this->assertSame(45000, PrintPricing::current()['techniques'][0]['price']);
        $this->assertSame(30000, $version->fresh()->data['techniques'][0]['price']);
        $this->assertNotSame($version->id, PrintPricing::currentVersionId());

        $controller->toggle(Request::create('/', 'POST', ['is_active' => false]), $technique);
        $this->assertFalse(PrintPricing::current()['techniques'][0]['is_active']);
        $this->assertSame(3, PrintPricingVersion::count());
    }

    public function test_unpriced_technique_does_not_inherit_legacy_matrix(): void
    {
        PrintTechnique::create(['name' => 'Cũ', 'slug' => 'cu']);
        PrintPricingVersion::create(['data' => ['cells' => [1 => [10 => 25000]]]]);
        $pricing = PrintPricing::current();
        $this->assertSame('flat', $pricing['mode']);
        $this->assertNull($pricing['techniques'][0]['price']);
        $this->assertNull(PrintPricing::currentVersionId());
    }

    public function test_form_renders_with_only_name_and_price_required(): void
    {
        $html = view('print.partials.technique-form', ['technique' => null])->render();
        $this->assertStringContainsString('name="name" required', $html);
        $this->assertStringContainsString('name="price" type="number" required', $html);
        $this->assertStringNotContainsString('min_dpi', $html);
    }
}
