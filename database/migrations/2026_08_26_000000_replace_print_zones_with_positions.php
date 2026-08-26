<?php

use App\Services\PrintPositions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Bỏ "vùng in" do chủ shop kéo khung; thay bằng bốn vị trí cố định.
 *
 * Xem App\Services\PrintPositions để biết vì sao. Ở đây chỉ có ba việc, và việc
 * thứ hai mới là việc khó:
 *
 *   1. Thêm `print_blanks.positions` — bốn ô tick thay cho bảng print_zones.
 *
 *   2. ĐỔI HỆ TOẠ ĐỘ CỦA MỌI THIẾT KẾ CŨ. Trước đây x/y tính từ góc trên trái
 *      của khung vùng in; giờ tính từ góc trên trái của cả tấm mockup. Bỏ qua
 *      bước này là mọi đơn đang chờ duyệt in lệch đúng bằng khoảng cách từ mép
 *      ảnh tới khung — thường là cả chục centimét.
 *
 *   3. Đổi tên trong ngữ pháp quy tắc giá của BẢN NHÁP. Các bản đã xuất bản
 *      không đụng tới: chúng là ảnh chụp bất biến, và PrintPricing đọc được cả
 *      tên cũ lẫn tên mới.
 *
 * Chiều xuống dựng lại được cái bảng, nhưng KHÔNG dựng lại được các vùng in đã
 * xoá — không có gì để suy ngược ra một khung mà người ta đã kéo bằng tay.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_blanks', function (Blueprint $table) {
            // null = chưa khai; PrintPositions::normalise() hiểu là đủ bốn vị trí.
            $table->json('positions')->nullable()->after('frame_height_mm');
        });

        // Bảng có thể đã bị bỏ bằng tay ở một môi trường nào đó; thiếu nó thì
        // không có gì để chuyển đổi, nhưng cột `positions` vẫn phải thêm.
        $zonesByBlank = Schema::hasTable('print_zones')
            ? DB::table('print_zones')->get()->groupBy('print_blank_id')
            : collect();

        $blanks = DB::table('print_blanks')->get()->keyBy('id');

        $this->fillBlankPositions($blanks, $zonesByBlank);
        $this->moveDesignsToFrameOrigin($blanks, $zonesByBlank);
        $this->renamePricingDraftKeys();

        Schema::dropIfExists('print_zones');
    }

    /**
     * Bốn ô tick suy từ những vùng phôi đó THẬT SỰ đang có.
     *
     * Cố ý không bật hết bốn vị trí cho mọi phôi: phôi chỉ khai mỗi vùng ngực là
     * phôi chủ shop chỉ định bán in ngực, và tự dưng mở bán in lưng sau một lần
     * cập nhật là bán thứ chưa ai định giá. Thiếu thì vào trang phôi tick thêm.
     */
    private function fillBlankPositions($blanks, $zonesByBlank): void
    {
        foreach ($blanks as $blank) {
            $zones = $zonesByBlank->get($blank->id, collect());

            $positions = $zones
                ->map(fn ($z) => $this->guessPosition($z->key . ' ' . $z->label))
                ->unique()
                ->values()
                ->all();

            DB::table('print_blanks')
                ->where('id', $blank->id)
                // Phôi chưa khai vùng nào thì để null — nghĩa là đủ bốn vị trí.
                ->update(['positions' => $positions ? json_encode(PrintPositions::normalise($positions)) : null]);
        }
    }

    /**
     * Dời toạ độ mọi hình từ gốc-vùng-in sang gốc-khung-ảnh.
     *
     * Khung vùng in cũ lưu vị trí bằng phần trăm trên tấm mockup (`box_x`), còn
     * hiệu chuẩn khung ảnh (`frame_width_mm`) cho biết tấm ấy rộng bao nhiêu mm
     * thật. Nhân hai thứ đó ra được gốc của vùng tính bằng mm, cộng vào toạ độ
     * cũ là ra toạ độ mới.
     *
     * Hình nào trỏ vào một vùng đã bị xoá thì giữ nguyên toạ độ — sai còn hơn
     * đoán bừa, và trang duyệt thiết kế vẫn mở được để người ta nhìn thấy.
     */
    private function moveDesignsToFrameOrigin($blanks, $zonesByBlank): void
    {
        DB::table('print_designs')->orderBy('id')->chunk(200, function ($designs) use ($blanks, $zonesByBlank) {
            foreach ($designs as $design) {
                $placements = json_decode((string) $design->placements, true);
                if (!is_array($placements) || !$placements) {
                    continue;
                }

                $blank = $blanks->get($design->print_blank_id);
                $zones = $zonesByBlank->get($design->print_blank_id, collect())->keyBy('key');

                $moved = array_map(function (array $p) use ($blank, $zones) {
                    $zone = $zones->get($p['zone'] ?? '');

                    $p['position'] = $zone
                        ? $this->guessPosition($zone->key . ' ' . $zone->label)
                        : $this->guessPosition((string) ($p['zone'] ?? ''));

                    if ($zone && $blank) {
                        $p['x_mm'] = (float) ($p['x_mm'] ?? 0)
                            + ((float) $zone->box_x / 100) * (int) $blank->frame_width_mm;
                        $p['y_mm'] = (float) ($p['y_mm'] ?? 0)
                            + ((float) $zone->box_y / 100) * (int) $blank->frame_height_mm;
                    }

                    unset($p['zone']);

                    return $p;
                }, $placements);

                DB::table('print_designs')
                    ->where('id', $design->id)
                    ->update(['placements' => json_encode($moved, JSON_UNESCAPED_UNICODE)]);
            }
        });
    }

    /**
     * `zone_keys` thành `position_keys`, `per: zone` thành `per: position`.
     *
     * Giá trị bên trong `zone_keys` cũng phải đổi: chúng là khoá vùng sinh ngẫu
     * nhiên kiểu "za1b2c3", không khớp với bốn khoá mới. Đổi tên trường mà giữ
     * nguyên giá trị là quy tắc im lặng ngừng chạy — kiểu hỏng khó thấy nhất.
     */
    private function renamePricingDraftKeys(): void
    {
        $row = DB::table('settings')->where('key', 'print_pricing_draft')->first();
        if (!$row) {
            return;
        }

        $draft = json_decode((string) $row->value, true);
        if (!is_array($draft) || empty($draft['rules'])) {
            return;
        }

        $zoneLabels = Schema::hasTable('print_zones')
            ? DB::table('print_zones')->pluck('label', 'key')
            : collect();

        $draft['rules'] = array_map(function (array $rule) use ($zoneLabels) {
            if (isset($rule['when']['zone_keys'])) {
                $rule['when']['position_keys'] = PrintPositions::normalise(
                    array_map(
                        fn ($key) => $this->guessPosition($key . ' ' . ($zoneLabels[$key] ?? '')),
                        (array) $rule['when']['zone_keys'],
                    ),
                );
                unset($rule['when']['zone_keys']);
            }

            if (($rule['apply']['per'] ?? null) === 'zone') {
                $rule['apply']['per'] = 'position';
            }

            return $rule;
        }, $draft['rules']);

        DB::table('settings')
            ->where('key', 'print_pricing_draft')
            ->update(['value' => json_encode($draft, JSON_UNESCAPED_UNICODE)]);
    }

    /**
     * Đoán vị trí mới từ tên vùng cũ.
     *
     * Thứ tự kiểm là cố ý: "ngực trái" có chữ "trái" nhưng là mặt trước, nên chỉ
     * coi là vai khi có kèm chữ vai/tay/sleeve. Đoán trượt thì rơi về mặt trước
     * — chỗ đông hình nhất, và là chỗ nhân viên duyệt thiết kế nhìn đầu tiên.
     */
    private function guessPosition(string $text): string
    {
        $t = Str::lower(Str::ascii($text));

        if (Str::contains($t, ['sau', 'lung', 'back'])) {
            return PrintPositions::BACK;
        }

        if (Str::contains($t, ['vai', 'tay', 'sleeve', 'shoulder'])) {
            if (Str::contains($t, ['phai', 'right'])) {
                return PrintPositions::SHOULDER_RIGHT;
            }
            if (Str::contains($t, ['trai', 'left'])) {
                return PrintPositions::SHOULDER_LEFT;
            }
        }

        return PrintPositions::FRONT;
    }

    public function down(): void
    {
        Schema::create('print_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_blank_id')->constrained()->cascadeOnDelete();
            $table->string('key', 40);
            $table->string('label', 80);
            $table->unsignedSmallInteger('width_mm');
            $table->unsignedSmallInteger('height_mm');
            $table->decimal('box_x', 5, 2);
            $table->decimal('box_y', 5, 2);
            $table->decimal('box_w', 5, 2);
            $table->decimal('box_h', 5, 2);
            $table->unsignedSmallInteger('max_placements')->default(10);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unique(['print_blank_id', 'key']);
        });

        Schema::table('print_blanks', function (Blueprint $table) {
            $table->dropColumn('positions');
        });
    }
};
