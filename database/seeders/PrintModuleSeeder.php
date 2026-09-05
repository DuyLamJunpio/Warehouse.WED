<?php

namespace Database\Seeders;

use App\Models\PrintFont;
use App\Models\PrintTechnique;
use App\Services\PrintPricing;
use Illuminate\Database\Seeder;

/** Tạo kỹ thuật và phông chữ ban đầu; shop nhập giá cố định trước khi bán. */
class PrintModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTechniques();
        $this->seedFonts();
        PrintPricing::publish('Khởi tạo kỹ thuật in — nhập giá để bắt đầu bán');
    }

    /** @return array<string,PrintTechnique> theo slug */
    private function seedTechniques(): array
    {
        $rows = [
            ['slug' => 'decal', 'name' => 'Decal chuyển nhiệt', 'max_colors' => null,
             'accepts_photo' => true, 'accepts_gradient' => true, 'needs_underbase' => true,
             'min_dpi' => 150, 'file_types' => 'png,pdf,svg', 'lead_days' => 3, 'moq' => 1,
             'description' => 'Ép nhiệt lên vải. Rẻ, nhanh, hợp đơn ít.'],

            ['slug' => 'dtg', 'name' => 'In kỹ thuật số DTG', 'max_colors' => null,
             'accepts_photo' => true, 'accepts_gradient' => true, 'needs_underbase' => true,
             'min_dpi' => 200, 'file_types' => 'png,tiff', 'lead_days' => 2, 'moq' => 1,
             'description' => 'In thẳng lên vải, bám màu mềm, hợp hình nhiều màu và ảnh chụp.'],

            ['slug' => 'lua', 'name' => 'In lụa', 'max_colors' => 6,
             'accepts_photo' => false, 'accepts_gradient' => false, 'needs_underbase' => true,
             'min_dpi' => 300, 'file_types' => 'ai,svg,pdf', 'lead_days' => 5, 'moq' => 20,
             'description' => 'Mỗi màu một khung. Rẻ nhất khi in số lượng lớn cùng mẫu.'],

            ['slug' => 'theu', 'name' => 'Thêu vi tính', 'max_colors' => 12,
             'accepts_photo' => false, 'accepts_gradient' => false, 'needs_underbase' => false,
             'min_dpi' => 300, 'file_types' => 'ai,svg,dst', 'lead_days' => 7, 'moq' => 10,
             'description' => 'Bền nhất, sang nhất. Chỉ hợp logo và chữ, không thêu được ảnh.'],
        ];

        $out = [];
        foreach ($rows as $i => $row) {
            $out[$row['slug']] = PrintTechnique::firstOrCreate(
                ['slug' => $row['slug']],
                $row + ['sort_order' => $i, 'is_active' => true],
            );
        }

        return $out;
    }

    /**
     * Phông chữ mở màn.
     *
     * Chỉ dùng phông hệ thống sẵn có nên chưa cần tệp woff2 nào — studio vẫn hiện
     * đúng mặt chữ, và xưởng nào cũng có sẵn mấy phông này. Muốn thêm phông riêng
     * thì tải tệp lên rồi khai thêm một dòng.
     */
    private function seedFonts(): void
    {
        $rows = [
            ['name' => 'Chữ không chân', 'family' => 'Arial, Helvetica, sans-serif'],
            ['name' => 'Chữ có chân', 'family' => 'Georgia, "Times New Roman", serif'],
            ['name' => 'Chữ máy đánh', 'family' => '"Courier New", monospace'],
            ['name' => 'Chữ đậm thể thao', 'family' => '"Arial Black", Impact, sans-serif'],
        ];

        foreach ($rows as $i => $row) {
            PrintFont::updateOrCreate(
                ['name' => $row['name']],
                $row + ['sort_order' => $i, 'is_active' => true],
            );
        }
    }

}
