<?php

namespace App\Models;

use App\Services\PrintPositions;
use App\Services\PrintPricing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Một mẫu áo khách đã thiết kế.
 *
 * Một bản ghi ở đây là MỘT MẪU, không phải một đơn hàng: khách đặt 30 áo cùng
 * mẫu thì vẫn là một thiết kế với qty = 30.
 *
 * Giá đã chốt được đóng băng vào `unit_price`, `total_price` và `price_breakdown`
 * kèm `pricing_version_id`. Không tính lại khi đọc — chủ shop sửa bảng giá sau đó
 * thì đơn này vẫn giữ nguyên con số khách đã nhìn thấy lúc trả tiền.
 */
class PrintDesign extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUS_LABELS = [
        self::STATUS_DRAFT => 'Bản nháp',
        self::STATUS_PENDING => 'Chờ duyệt thiết kế',
        self::STATUS_APPROVED => 'Đã duyệt',
        self::STATUS_REJECTED => 'Từ chối',
    ];

    protected $fillable = [
        'code', 'invoice_id', 'pending_payment_ref', 'print_blank_id', 'print_technique_id', 'color_name', 'color_tone',
        'size', 'ink_colors', 'qty', 'placements', 'preview_path',
        'pricing_version_id', 'price_breakdown', 'unit_price', 'total_price',
        'review_status', 'review_note', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'placements' => 'array',
        'price_breakdown' => 'array',
        'ink_colors' => 'integer',
        'qty' => 'integer',
        'unit_price' => 'integer',
        'total_price' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    /** Hoa don chua mau nay; rong = da chot thiet ke nhung chua dat hang. */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function blank(): BelongsTo
    {
        return $this->belongsTo(PrintBlank::class, 'print_blank_id');
    }

    public function technique(): BelongsTo
    {
        return $this->belongsTo(PrintTechnique::class, 'print_technique_id');
    }

    public function pricingVersion(): BelongsTo
    {
        return $this->belongsTo(PrintPricingVersion::class, 'pricing_version_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Mã ngắn khách chia sẻ lại được; tránh ký tự dễ đọc nhầm. */
    public static function newCode(): string
    {
        do {
            $code = 'IN' . Str::upper(Str::random(8));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->review_status] ?? $this->review_status;
    }

    /**
     * Bảng toạ độ giao cho thợ in.
     *
     * Cố ý KHÔNG dựng file in ở đây. Máy chủ không có ImageMagick, và mỗi lần
     * resample là một lần sai màu. Xưởng nhận ba thứ: file gốc khách tải lên,
     * ảnh preview để đối chiếu, và bảng mm này — rồi tự đặt trong phần mềm in
     * của họ. Đó là cách các xưởng thật sự làm việc.
     */
    public function productionSheet(): array
    {
        $rows = [];

        foreach ((array) $this->placements as $p) {
            // `zone` là tên cũ của trường này, còn trong vài bản ghi rất cũ.
            $key = $p['position'] ?? $p['zone'] ?? '';
            $position = PrintPositions::get($key);

            $isText = ($p['kind'] ?? 'image') === 'text';

            $rows[] = [
                'kind' => $isText ? 'text' : 'image',
                'position' => PrintPositions::label($key),
                'position_max_mm' => $position
                    ? 'tối đa ' . $position['max_width_mm'] . ' × ' . $position['max_height_mm'] . ' mm'
                    : null,
                // Với chữ, "tên" là chính nội dung — đó là thứ thợ cần đọc.
                'asset_name' => $isText ? ($p['text_content'] ?? '') : ($p['asset_name'] ?? null),
                'asset_url' => $isText ? null : ($p['asset_url'] ?? null),
                'text_font_name' => $p['text_font_name'] ?? null,
                'text_color' => $p['text_color'] ?? null,
                'x_mm' => round((float) ($p['x_mm'] ?? 0), 1),
                'y_mm' => round((float) ($p['y_mm'] ?? 0), 1),
                'width_mm' => round((float) ($p['w_mm'] ?? 0), 1),
                'height_mm' => round((float) ($p['h_mm'] ?? 0), 1),
                'rotation' => round((float) ($p['rotation'] ?? 0), 1),
                // Chữ là vector: không có pixel nguồn, không có DPI để mà thiếu.
                'source_px' => $isText ? 'vector' : ($p['asset_width_px'] ?? null) . '×' . ($p['asset_height_px'] ?? null),
                'dpi' => !$isText && isset($p['asset_width_px'], $p['w_mm']) && (float) $p['w_mm'] > 0
                    ? (int) round($p['asset_width_px'] / ((float) $p['w_mm'] / 25.4))
                    : null,
            ];
        }

        return $rows;
    }

    /** Khung bao mỗi vị trí, để nhân viên kiểm nhanh khổ in đã tính tiền. */
    public function positionBoxes(): array
    {
        $out = [];

        foreach ($this->placementsByPosition() as $key => $list) {
            $box = PrintPricing::boundingBox($list);

            $out[$key] = [
                'label' => PrintPositions::label($key),
                'width_mm' => round($box['w'], 1),
                'height_mm' => round($box['h'], 1),
                // Chỗ đặt thật trên áo, tính từ góc trên trái khung ảnh phôi.
                'x_mm' => round($box['x'], 1),
                'y_mm' => round($box['y'], 1),
            ];
        }

        return $out;
    }

    /**
     * Gom hình theo vị trí in, giữ nguyên thứ tự khách đặt vào.
     *
     * @return array<string, array<int, array>>
     */
    public function placementsByPosition(): array
    {
        $out = [];
        foreach ((array) $this->placements as $p) {
            $out[$p['position'] ?? $p['zone'] ?? ''][] = $p;
        }

        return $out;
    }

    /**
     * Khung ảnh của phôi — gốc toạ độ mà mọi x/y trong bảng thợ in đọc tính từ đó.
     *
     * Từ khi bỏ khung vùng in, toạ độ không còn tính từ một cái khung do người
     * quản trị kéo nữa mà tính từ góc trên trái của cả tấm mockup. Thợ phải biết
     * tấm ấy ứng với bao nhiêu milimét thật thì con số mới có nghĩa.
     */
    public function frameSizeMm(): ?string
    {
        $blank = $this->blank;

        return $blank ? $blank->frame_width_mm . ' × ' . $blank->frame_height_mm . ' mm' : null;
    }

    /**
     * File sản xuất, dựng thành SVG ở tỉ lệ 1:1 milimét.
     *
     * Vì sao SVG chứ không phải ảnh bitmap:
     *
     *   • Nó là định dạng VĂN BẢN — ghép bằng chuỗi, không cần ImageMagick, chạy
     *     được trên máy chủ không cài gì thêm.
     *   • Chữ nằm trong `<text>` thật. Thợ mở bằng Illustrator hoặc CorelDraw,
     *     bấm convert to outlines là in được — không phải gõ lại tay.
     *   • Không lần nào resample nên không lần nào sai màu.
     *
     * Mỗi vị trí in là một `<g>` riêng, xếp cạnh nhau. Trong mỗi nhóm, toạ độ
     * được DỜI VỀ GỐC KHUNG BAO của chính nó: một mảnh in phải nằm sát mép tệp
     * để thợ cắt, chứ không lạc giữa một tờ trống to bằng cả chiếc áo. Chỗ đặt
     * thật trên áo không mất đi — nó nằm trong nhãn ngay trên mỗi nhóm.
     */
    public function toSvg(): string
    {
        /** Khoảng hở giữa hai vị trí, đủ để thợ cắt rời mà không chạm nét. */
        $gap = 20;
        $offsetX = 0;
        $totalHeight = 0;
        $groups = [];

        foreach ($this->placementsByPosition() as $key => $placements) {
            $box = PrintPricing::boundingBox($placements);
            $width = max($box['w'], 1);
            $height = max($box['h'], 1);

            $body = '';
            foreach ($placements as $p) {
                $body .= $this->svgPlacement($p, -$box['x'], -$box['y']);
            }

            $groups[] = sprintf(
                '<g transform="translate(%s 0)">'
                . '<rect x="0" y="0" width="%s" height="%s" fill="none" stroke="#cccccc" stroke-width="0.2" stroke-dasharray="2 2"/>'
                . '<text x="0" y="-4" font-family="sans-serif" font-size="4" fill="#999999">%s — %s×%s mm, đặt tại %s/%s mm trên áo</text>'
                . '%s</g>',
                round($offsetX, 2),
                round($width, 2),
                round($height, 2),
                self::xml(PrintPositions::label($key)),
                round($width, 1),
                round($height, 1),
                round($box['x'], 1),
                round($box['y'], 1),
                $body,
            );

            $offsetX += $width + $gap;
            $totalHeight = max($totalHeight, $height);
        }

        $width = max($offsetX - $gap, 10);
        $height = max($totalHeight, 10);

        return sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" '
            . 'width="%smm" height="%smm" viewBox="0 %s %s %s">' . "\n"
            . '<!-- %s · %s · %s · size %s · %d áo. Toạ độ tính bằng milimét, tỉ lệ 1:1. -->' . "\n"
            . '%s' . "\n</svg>\n",
            $width,
            $height + 8,
            -8,
            $width,
            $height + 8,
            self::xml($this->code),
            self::xml((string) $this->blank?->name),
            self::xml($this->color_name),
            self::xml($this->size),
            $this->qty,
            implode("\n", $groups),
        );
    }

    /**
     * Một hình hoặc một dòng chữ, đặt đúng chỗ trong nhóm của vị trí in.
     *
     * `$dx`/`$dy` là phép dời về gốc khung bao của nhóm — xem toSvg().
     */
    private function svgPlacement(array $p, float $dx = 0, float $dy = 0): string
    {
        $x = round((float) ($p['x_mm'] ?? 0) + $dx, 2);
        $y = round((float) ($p['y_mm'] ?? 0) + $dy, 2);
        $w = round((float) ($p['w_mm'] ?? 0), 2);
        $h = round((float) ($p['h_mm'] ?? 0), 2);
        $rotation = round((float) ($p['rotation'] ?? 0), 2);

        // Xoay quanh tâm hình, đúng như studio hiển thị cho khách.
        $transform = $rotation != 0.0
            ? sprintf(' transform="rotate(%s %s %s)"', $rotation, $x + $w / 2, $y + $h / 2)
            : '';

        if (($p['kind'] ?? 'image') === 'text') {
            /*
             * viewBox trong `<svg>` lồng khiến chữ co vừa khít khung — giống hệt
             * cách studio vẽ, nên cái khách duyệt và cái thợ in ra là một.
             */
            return sprintf(
                '<svg x="%s" y="%s" width="%s" height="%s" viewBox="0 0 200 60" preserveAspectRatio="xMidYMid meet"%s>'
                . '<text x="100" y="30" text-anchor="middle" dominant-baseline="central" '
                . 'font-family="%s" font-size="44" fill="%s">%s</text></svg>',
                $x, $y, $w, $h,
                $transform,
                self::xml($p['text_font_family'] ?? 'sans-serif'),
                self::xml($p['text_color'] ?? '#000000'),
                self::xml($p['text_content'] ?? ''),
            );
        }

        // Ảnh tham chiếu theo link chứ không nhúng base64: tệp nhẹ, và thợ luôn
        // lấy đúng bản gốc thay vì một bản đã đi qua thêm một lần mã hoá.
        return sprintf(
            '<image xlink:href="%s" x="%s" y="%s" width="%s" height="%s" preserveAspectRatio="xMidYMid meet"%s/>',
            self::xml($p['asset_url'] ?? ''),
            $x, $y, $w, $h,
            $transform,
        );
    }

    private static function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
