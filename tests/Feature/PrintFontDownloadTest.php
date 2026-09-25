<?php

namespace Tests\Feature;

use App\Http\Controllers\PrintAssetController;
use App\Models\PrintFont;
use App\Services\StorefrontNotifier;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class PrintFontDownloadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('print_fonts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('family')->default('sans-serif');
            $table->string('file_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        config(['filesystems.default' => 'local']);
        Storage::fake('local');
        $this->mock(StorefrontNotifier::class)->shouldReceive('markDirty')->zeroOrMoreTimes();
    }

    public function test_an_uploaded_font_downloads_under_a_readable_name(): void
    {
        Storage::put('public/fonts/cc2e58de.otf', 'OTTO' . str_repeat("\0", 64));
        $font = $this->font('BHN Editorial New Ultra Light Italic', 'public/fonts/cc2e58de.otf');

        $response = app(PrintAssetController::class)->downloadFont($font);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString(
            'filename=bhn-editorial-new-ultra-light-italic.otf',
            $response->headers->get('Content-Disposition'),
        );
    }

    public function test_a_system_font_has_no_file_to_download(): void
    {
        $this->expectException(NotFoundHttpException::class);

        app(PrintAssetController::class)->downloadFont($this->font('Arial', null));
    }

    public function test_only_uploaded_fonts_get_a_font_face(): void
    {
        $uploaded = $this->font('Editorial', 'public/fonts/editorial.otf');
        $system = $this->font('Arial', null);

        $this->assertNull($system->fontFace());
        $this->assertSame(
            sprintf('@font-face{font-family:"print-font-%d";src:url("%s");}', $uploaded->id, Storage::url('public/fonts/editorial.otf')),
            PrintFont::fontFaceCss([$uploaded, $system]),
        );
    }

    private function font(string $name, ?string $path): PrintFont
    {
        $font = PrintFont::create(['name' => $name, 'family' => 'Arial, sans-serif', 'file_path' => $path]);

        if ($path) {
            $font->update(['family' => sprintf('"print-font-%d", sans-serif', $font->id)]);
        }

        return $font;
    }
}
