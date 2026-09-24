<?php

namespace Tests\Feature;

use App\Http\Controllers\PrintAssetController;
use App\Models\PrintFont;
use App\Services\StorefrontNotifier;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PrintFontUploadTest extends TestCase
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

    public function test_valid_otf_upload_is_accepted_even_if_php_reports_an_unhelpful_mime_type(): void
    {
        $response = $this->storeFont(
            UploadedFile::fake()->createWithContent('BHN-Editorial.otf', "OTTO" . str_repeat("\0", 64)),
        );

        $this->assertSame(200, $response->getStatusCode());
        $font = PrintFont::firstOrFail();
        $this->assertStringEndsWith('.otf', $font->file_path);
        Storage::disk('local')->assertExists($font->file_path);
    }

    public function test_font_upload_rejects_a_file_with_a_font_extension_but_no_font_signature(): void
    {
        $this->expectException(ValidationException::class);

        $this->storeFont(UploadedFile::fake()->createWithContent('not-a-font.otf', 'not a font'));
    }

    private function storeFont(UploadedFile $file)
    {
        return app(PrintAssetController::class)->storeFont(Request::create(
            '/print/fonts',
            'POST',
            ['name' => 'BHN Editorial'],
            [],
            ['file' => $file],
        ));
    }
}
