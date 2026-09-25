<?php

namespace Tests\Feature;

use App\Http\Controllers\PrintDesignController;
use App\Models\PrintDesign;
use App\Models\PrintFont;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class PrintDesignSvgTest extends TestCase
{
    private const FONT_PATH = 'public/fonts/editorial.otf';

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
    }

    public function test_each_print_position_is_its_own_file(): void
    {
        $design = $this->design();

        $front = $design->toSvg('front');
        $back = $design->toSvg('back');

        $this->assertStringContainsString('Duy Lam Dao', $front);
        $this->assertStringNotContainsString('shirt-back.jpg', $front);
        $this->assertStringContainsString('shirt-back.jpg', $back);
        $this->assertStringNotContainsString('Duy Lam Dao', $back);
        $this->assertSame(['front', 'back'], $design->positionKeys());
    }

    public function test_the_file_carries_the_font_the_customer_chose(): void
    {
        $font = PrintFont::create([
            'name' => 'BHN Editorial New Ultra Light Italic',
            'family' => 'sans-serif',
            'file_path' => self::FONT_PATH,
        ]);
        $font->update(['family' => sprintf('"print-font-%d", sans-serif', $font->id)]);

        $xpath = $this->parse($this->design($font)->toSvg('front'));

        $this->assertStringContainsString(
            sprintf('@font-face{font-family:"print-font-%d";src:url("%s");}', $font->id, Storage::url(self::FONT_PATH)),
            $xpath->evaluate('string(//svg:style)'),
        );
        $this->assertSame(
            sprintf('"BHN Editorial New Ultra Light Italic", "print-font-%d", sans-serif', $font->id),
            $xpath->evaluate('string(//svg:svg/svg:text/@font-family)'),
        );
    }

    public function test_each_text_line_names_its_font_for_the_review_page(): void
    {
        $font = PrintFont::create([
            'name' => 'BHN Editorial New Ultra Light Italic',
            'family' => 'sans-serif',
            'file_path' => self::FONT_PATH,
        ]);

        $lines = $this->design($font)->textLines();

        $this->assertCount(1, $lines);
        $this->assertSame('Duy Lam Dao', $lines[0]['text']);
        $this->assertSame('BHN Editorial New Ultra Light Italic', $lines[0]['font_name']);
        $this->assertTrue($lines[0]['font']->is($font));
    }

    public function test_download_is_named_after_the_position(): void
    {
        $response = app(PrintDesignController::class)->svg($this->design(), 'back');

        $this->assertSame('attachment; filename="INTEST0001-mat-sau.svg"', $response->headers->get('Content-Disposition'));
        $this->parse($response->getContent());
    }

    public function test_a_position_the_design_does_not_use_is_not_found(): void
    {
        $this->expectException(NotFoundHttpException::class);

        app(PrintDesignController::class)->svg($this->design(), 'shoulder_left');
    }

    private function design(?PrintFont $font = null): PrintDesign
    {
        $design = new PrintDesign([
            'code' => 'INTEST0001',
            'color_name' => 'Trắng',
            'size' => 'S',
            'qty' => 1,
            'placements' => [
                [
                    'position' => 'front',
                    'kind' => 'text',
                    'text_content' => 'Duy Lam Dao',
                    'text_font_id' => $font?->id,
                    'text_font_name' => $font?->name,
                    'text_font_family' => $font?->family,
                    'text_color' => '#1a1614',
                    'x_mm' => 148, 'y_mm' => 249.4, 'w_mm' => 224, 'h_mm' => 53.76, 'rotation' => 0,
                ],
                [
                    'position' => 'back',
                    'kind' => 'image',
                    'asset_url' => 'https://cdn.test/shirt-back.jpg',
                    'x_mm' => 172, 'y_mm' => 217.8, 'w_mm' => 176, 'h_mm' => 176, 'rotation' => 0,
                ],
            ],
        ]);
        $design->setRelation('blank', null);

        return $design;
    }

    /** Parsing doubles as a check that the file is well-formed XML. */
    private function parse(string $svg): DOMXPath
    {
        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML($svg), 'SVG is not well-formed XML');
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('svg', 'http://www.w3.org/2000/svg');

        return $xpath;
    }
}
