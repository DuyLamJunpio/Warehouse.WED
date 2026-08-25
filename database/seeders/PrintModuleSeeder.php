<?php

namespace Database\Seeders;

use App\Models\PrintFont;
use App\Models\PrintSizeTier;
use App\Models\PrintTechnique;
use App\Services\PrintPricing;
use Illuminate\Database\Seeder;

/**
 * Dữ liệu mở màn cho module in áo.
 *
 * Module mở ra trống trơn thì chủ shop không biết bắt đầu từ đâu: một ma trận
 * giá không hàng không cột chẳng gợi ý được gì. Cài sẵn bốn kỹ thuật thông dụng
 * với ràng buộc điền đúng, bốn bậc khổ, và vài quy tắc phụ phí có thật trong
 * nghề — sửa hoặc tắt cái không dùng thì nhanh hơn dựng từ số không nhiều.
 *
 * Chạy lại được nhiều lần: dò theo slug nên không nhân bản dữ liệu.
 *
 *     php artisan db:seed --class=PrintModuleSeeder
 */
class PrintModuleSeeder extends Seeder
{
    public function run(): void
    {
        $techniques = $this->seedTechniques();
        $tiers = $this->seedTiers();
        $this->seedFonts();

        PrintPricing::putDraft([
            'cells' => $this->cells($techniques, $tiers),
            'rules' => $this->rules($techniques),
            'qty_tiers' => [
                ['from' => 10, 'pct' => 5],
                ['from' => 25, 'pct' => 10],
                ['from' => 50, 'pct' => 15],
            ],
            'rounding' => 1000,
            'min_charge' => 0,
        ]);

        PrintPricing::publish('Bảng giá khởi tạo');
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
            $out[$row['slug']] = PrintTechnique::updateOrCreate(
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

    /** @return array<string,PrintSizeTier> theo tên */
    private function seedTiers(): array
    {
        $rows = [
            ['name' => 'A6', 'width_mm' => 105, 'height_mm' => 148],
            ['name' => 'A5', 'width_mm' => 148, 'height_mm' => 210],
            ['name' => 'A4', 'width_mm' => 210, 'height_mm' => 297],
            ['name' => 'A3', 'width_mm' => 297, 'height_mm' => 420],
        ];

        $out = [];
        foreach ($rows as $i => $row) {
            $out[$row['name']] = PrintSizeTier::updateOrCreate(
                ['name' => $row['name']],
                $row + ['sort_order' => $i, 'is_active' => true],
            );
        }

        return $out;
    }

    /**
     * Ma trận giá. Ô vắng mặt = kỹ thuật đó không nhận khổ đó, studio tự ẩn lựa
     * chọn. Thêu bỏ trống A4 và A3 vì khổ thêu lớn vừa lâu vừa dễ nhăn vải.
     */
    private function cells(array $tech, array $tiers): array
    {
        $table = [
            'decal' => ['A6' => 20000, 'A5' => 35000, 'A4' => 55000, 'A3' => 90000],
            'dtg' => ['A6' => 25000, 'A5' => 45000, 'A4' => 75000, 'A3' => 130000],
            'lua' => ['A6' => 35000, 'A5' => 50000, 'A4' => 70000, 'A3' => 110000],
            'theu' => ['A6' => 45000, 'A5' => 70000, 'A4' => null, 'A3' => null],
        ];

        $cells = [];
        foreach ($table as $slug => $row) {
            foreach ($row as $tierName => $price) {
                if ($price === null) {
                    continue;
                }
                $cells[$tech[$slug]->id][$tiers[$tierName]->id] = $price;
            }
        }

        return $cells;
    }

    /** Quy tắc phụ phí có thật trong nghề, để chủ shop thấy ngữ pháp dùng thế nào. */
    private function rules(array $tech): array
    {
        return [
            [
                'id' => 'underbase',
                'label' => 'Lót trắng cho áo màu tối',
                'enabled' => true,
                'when' => ['tone' => 'dark', 'technique_ids' => [$tech['decal']->id, $tech['dtg']->id]],
                'apply' => ['kind' => PrintPricing::KIND_ADD, 'amount' => 15000, 'per' => PrintPricing::PER_ZONE],
            ],
            [
                'id' => 'back-zone',
                'label' => 'Mặt lưng khó căn hơn',
                'enabled' => true,
                'when' => ['zone_keys' => ['back']],
                'apply' => ['kind' => PrintPricing::KIND_MULTIPLY, 'amount' => 1.2, 'per' => PrintPricing::PER_ZONE],
            ],
            [
                'id' => 'screen-extra-ink',
                'label' => 'In lụa — từ màu mực thứ 2',
                'enabled' => true,
                'when' => ['technique_ids' => [$tech['lua']->id], 'ink_colors_from' => 2],
                'apply' => ['kind' => PrintPricing::KIND_ADD, 'amount' => 20000, 'per' => PrintPricing::PER_INK_COLOR],
            ],
            [
                'id' => 'small-order',
                'label' => 'Phụ phí đơn lẻ dưới 5 áo',
                'enabled' => true,
                'when' => ['qty_to' => 4],
                'apply' => ['kind' => PrintPricing::KIND_ADD, 'amount' => 30000, 'per' => PrintPricing::PER_ORDER],
            ],
            [
                'id' => 'rush',
                'label' => 'Hàng gấp (bật tay khi cần)',
                'enabled' => false,
                'when' => [],
                'apply' => ['kind' => PrintPricing::KIND_PERCENT, 'amount' => 20, 'per' => PrintPricing::PER_ORDER],
            ],
        ];
    }
}
