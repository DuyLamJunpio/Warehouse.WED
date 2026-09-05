<?php

namespace App\Services;

use App\Models\PrintPricingVersion;
use App\Models\PrintBlank;
use App\Models\PrintSizeTier;
use App\Models\PrintTechnique;
use App\Models\Setting;

/**
 * Bộ máy tính tiền in — nguồn sự thật duy nhất về giá.
 *
 * Web bán hàng có một bản dịch của lớp này bằng TypeScript để hiện giá xem
 * trước trong studio, nhưng con số tính tiền thật luôn dựng lại ở đây. Đúng
 * khuôn mà phí giao hàng đang chạy (App\Models\Setting::shippingFeeFor).
 *
 * ─── Vì sao không phải là một mã hàng "dịch vụ in" ───────────────────
 *
 * Khi khách được tự chọn chỗ in, tiền in phụ thuộc cùng lúc vào kỹ thuật, khổ
 * in, vị trí nào trên áo, tông màu áo, số màu mực và số lượng. Không biến thể nào
 * phủ được tổ hợp đó. Nên đây là một hàm, và phần chủ shop chỉnh được là DỮ
 * LIỆU đầu vào của hàm chứ không phải bản thân hàm.
 *
 * ─── Vì sao không phải là trình soạn công thức ───────────────────────
 *
 * Cho chủ shop gõ công thức nghe thì linh hoạt, thực tế là một ngôn ngữ lập
 * trình mini: không ai dám đụng vào, và hai bản cài đặt (PHP ở đây, TypeScript
 * bên web) sẽ diễn giải khác nhau. Thay vào đó là một NGỮ PHÁP ĐÓNG — tập điều
 * kiện hữu hạn, tập tác động hữu hạn — mỗi quy tắc là một dòng chọn từ ô thả
 * xuống. Vì nó là dữ liệu chứ không phải mã, hai bên tính ra cùng một số.
 *
 * ─── Sáu bước, thứ tự CỐ ĐỊNH ────────────────────────────────────────
 *
 *   1. giá phôi (+ phụ thu size)
 *   2. giá in cơ bản — ma trận kỹ thuật × bậc khổ, tính cho từng vị trí
 *   3. phụ phí CỘNG
 *   4. hệ số NHÂN
 *   5. chiết khấu số lượng
 *   6. sàn giá rồi làm tròn
 *
 * Thứ tự này không cho cấu hình. Mở ra là hai bản cài đặt tính lệch nhau, và
 * lệch một đồng thì khách trả một số còn hoá đơn ghi số khác.
 */
class PrintPricing
{
    /** Chế độ giá gọn: một mức phí in cho mỗi cặp phôi + kỹ thuật. */
    public const MODE_SIMPLE = 'simple';

    /** Khoá của bản nháp đang sửa trong bảng settings. */
    public const DRAFT_KEY = 'print_pricing_draft';

    /** Cách tính của một quy tắc. */
    public const KIND_ADD = 'add';
    public const KIND_MULTIPLY = 'multiply';
    public const KIND_PERCENT = 'percent';

    /** Quy tắc tính trên đơn vị nào. */
    public const PER_ORDER = 'order';
    public const PER_SHIRT = 'shirt';
    public const PER_POSITION = 'position';
    public const PER_PLACEMENT = 'placement';
    public const PER_INK_COLOR = 'inkColor';

    /**
     * Tên cũ của PER_POSITION, hồi vị trí in còn là "vùng in" chủ shop tự kéo.
     *
     * Các bản bảng giá đã xuất bản là ảnh chụp BẤT BIẾN và vẫn còn chữ này bên
     * trong. Không sửa chúng — chỉ đọc hiểu, xem normalisePer().
     */
    public const PER_ZONE_LEGACY = 'zone';

    public const PER_LABELS = [
        self::PER_ORDER => 'mỗi đơn',
        self::PER_SHIRT => 'mỗi áo',
        self::PER_POSITION => 'mỗi vị trí',
        self::PER_PLACEMENT => 'mỗi hình',
        self::PER_INK_COLOR => 'mỗi màu mực',
    ];

    /** Ba đơn vị này tính riêng từng vị trí; hai đơn vị còn lại tính trên cả đơn. */
    private const PER_POSITION_SCOPED = [self::PER_POSITION, self::PER_PLACEMENT, self::PER_INK_COLOR];

    /** Đọc `per` của một quy tắc, hiểu cả tên cũ lẫn tên mới. */
    public static function normalisePer(?string $per): string
    {
        return match ($per) {
            null => self::PER_ORDER,
            self::PER_ZONE_LEGACY => self::PER_POSITION,
            default => $per,
        };
    }

    // ── Bản nháp và bản đã xuất bản ──────────────────────────────────

    /**
     * Phần bảng giá chủ shop đang sửa: ma trận ô giá, quy tắc, chiết khấu.
     *
     * Nằm ở settings chứ không ở print_pricing_versions vì các bản version là
     * BẤT BIẾN — chúng là ảnh chụp đã phát hành, đơn cũ đang trỏ vào.
     */
    public static function draftDefaults(): array
    {
        return [
            'mode' => self::MODE_SIMPLE,
            'blank_technique_prices' => [],
            // Các khoá cũ giữ lại để những bản giá đã xuất bản vẫn đọc được.
            'cells' => [],
            'rules' => [],
            'qty_tiers' => [],
            'rounding' => 1000,
            'min_charge' => 0,
        ];
    }

    public static function draft(): array
    {
        $stored = (array) (Setting::where('key', self::DRAFT_KEY)->value('value') ?? []);

        return array_merge(self::draftDefaults(), $stored);
    }

    public static function putDraft(array $draft): void
    {
        Setting::updateOrCreate(
            ['key' => self::DRAFT_KEY],
            ['value' => array_merge(self::draftDefaults(), $draft)],
        );
    }

    /**
     * Đóng băng bản nháp thành một phiên bản mới.
     *
     * Chụp TRỌN kỹ thuật và bậc khổ vào snapshot chứ không chỉ giữ id: chủ shop
     * sửa "A4" thành 210×310mm sau khi xuất bản mà snapshot chỉ có id thì đơn cũ
     * tính lại ra con số khác — đúng thứ mà việc đánh phiên bản sinh ra để chặn.
     */
    public static function publish(?string $note = null, ?int $userId = null): PrintPricingVersion
    {
        return PrintPricingVersion::create([
            'data' => self::snapshot(),
            'note' => $note,
            'published_by' => $userId,
            'published_at' => now(),
        ]);
    }

    /** Bản nháp + kỹ thuật + bậc khổ hiện tại, gộp thành một khối tự đủ. */
    public static function snapshot(): array
    {
        $draft = self::draft();

        $draft['mode'] = self::MODE_SIMPLE;
        $draft['blank_technique_prices'] = self::resolvedBlankTechniquePrices($draft);

        $draft['techniques'] = PrintTechnique::query()
            ->orderBy('sort_order')->orderBy('id')
            ->get()
            ->map(fn (PrintTechnique $t) => $t->toPricingArray())
            ->all();

        $draft['tiers'] = PrintSizeTier::query()
            ->where('is_active', true)
            ->orderBy('sort_order')->orderBy('id')
            ->get()
            ->map(fn (PrintSizeTier $t) => $t->toPricingArray())
            ->all();

        return $draft;
    }

    /**
     * Bảng giá đang có hiệu lực.
     *
     * Chưa xuất bản lần nào thì lùi về ảnh chụp của bản nháp: shop mới cài đặt
     * vẫn báo giá được ngay thay vì trả về 0 đồng cho mọi đơn — một cửa hàng bán
     * đồ miễn phí là hỏng nặng hơn nhiều so với một bảng giá chưa đóng dấu.
     */
    public static function current(): array
    {
        $version = PrintPricingVersion::latestPublished();
        $pricing = $version ? (array) $version->data : self::snapshot();

        // Bản giá cũ dùng ma trận theo khổ. Khi đọc lại, chuyển nó sang một mức
        // giá cố định cho từng phôi + kỹ thuật để lần cập nhật này không làm mất
        // khả năng báo giá cho dữ liệu đã có.
        if (($pricing['mode'] ?? null) !== self::MODE_SIMPLE) {
            $pricing['mode'] = self::MODE_SIMPLE;
            $pricing['blank_technique_prices'] = self::resolvedBlankTechniquePrices($pricing);
        }

        return $pricing;
    }

    /**
     * Giá phôi + kỹ thuật đã hoà đủ cho màn hình quản trị và API.
     *
     * Ô đã được lưu, kể cả null, luôn được ưu tiên. Nhờ vậy người bán có thể
     * xoá một mức giá cũ mà không bị giá ma trận cũ tự động quay lại.
     */
    public static function resolvedBlankTechniquePrices(?array $pricing = null): array
    {
        $pricing = $pricing ?? self::draft();
        $explicit = (array) ($pricing['blank_technique_prices'] ?? []);
        $cells = (array) ($pricing['cells'] ?? []);
        $tiers = collect((array) ($pricing['tiers'] ?? []));
        if ($tiers->isEmpty()) {
            $tiers = PrintSizeTier::query()
                ->where('is_active', true)
                ->get()
                ->map(fn (PrintSizeTier $tier) => $tier->toPricingArray());
        }
        $tiers = $tiers
            ->sortBy(fn ($tier) => ((int) ($tier['width_mm'] ?? 0)) * ((int) ($tier['height_mm'] ?? 0)));

        $legacyPrices = [];
        foreach ($cells as $techniqueId => $row) {
            foreach ($tiers as $tier) {
                $tierId = $tier['id'] ?? null;
                if ($tierId !== null && array_key_exists($tierId, (array) $row) && $row[$tierId] !== null) {
                    $legacyPrices[(string) $techniqueId] = max(0, (int) $row[$tierId]);
                    break;
                }
            }
        }

        $prices = [];
        $blanks = PrintBlank::with('techniques')->get();
        foreach ($blanks as $blank) {
            $blankKey = (string) $blank->id;
            $savedRow = (array) ($explicit[$blankKey] ?? $explicit[$blank->id] ?? []);

            foreach ($blank->techniques as $technique) {
                $techniqueKey = (string) $technique->id;
                if (array_key_exists($techniqueKey, $savedRow) || array_key_exists($technique->id, $savedRow)) {
                    $value = array_key_exists($techniqueKey, $savedRow)
                        ? $savedRow[$techniqueKey]
                        : $savedRow[$technique->id];
                    $prices[$blankKey][$techniqueKey] = $value === null ? null : max(0, (int) $value);
                    continue;
                }

                if (array_key_exists($techniqueKey, $legacyPrices)) {
                    $prices[$blankKey][$techniqueKey] = $legacyPrices[$techniqueKey];
                }
            }
        }

        return $prices;
    }

    /** Đọc một giá cố định từ snapshot, hiểu cả khoá JSON dạng chuỗi và số. */
    public static function simplePriceFor(array $pricing, int $blankId, int $techniqueId): ?int
    {
        $rows = (array) ($pricing['blank_technique_prices'] ?? []);
        $row = $rows[(string) $blankId] ?? $rows[$blankId] ?? null;

        if (is_array($row) && (array_key_exists((string) $techniqueId, $row) || array_key_exists($techniqueId, $row))) {
            $value = array_key_exists((string) $techniqueId, $row) ? $row[(string) $techniqueId] : $row[$techniqueId];

            return $value === null ? null : max(0, (int) $value);
        }

        // Snapshot cũ chưa có bảng giá theo phôi. Dùng bậc khổ nhỏ nhất đang có
        // làm cầu nối tạm thời; khi người bán bấm Lưu nháp, giá sẽ được chốt
        // thành dữ liệu mới và không còn phụ thuộc vào kích thước.
        $tiers = collect((array) ($pricing['tiers'] ?? []))
            ->sortBy(fn ($tier) => ((int) ($tier['width_mm'] ?? 0)) * ((int) ($tier['height_mm'] ?? 0)));
        $cells = (array) ($pricing['cells'][$techniqueId] ?? $pricing['cells'][(string) $techniqueId] ?? []);
        foreach ($tiers as $tier) {
            $tierId = $tier['id'] ?? null;
            if ($tierId !== null && array_key_exists($tierId, $cells) && $cells[$tierId] !== null) {
                return max(0, (int) $cells[$tierId]);
            }
        }

        return null;
    }

    public static function currentVersionId(): ?int
    {
        return PrintPricingVersion::latestPublished()?->id;
    }

    // ── Hình học ─────────────────────────────────────────────────────

    /**
     * Khung bao trục-thẳng của MỘT hình đã xoay, tính bằng mm.
     *
     * Hình xoay 45° chiếm chỗ rộng hơn chính nó, và thợ in phải cắt decal theo
     * phần chiếm chỗ đó — nên tính tiền theo hình chưa xoay là tính thiếu.
     */
    public static function placementBox(array $p): array
    {
        $rad = deg2rad((float) ($p['rotation'] ?? 0));
        $cos = abs(cos($rad));
        $sin = abs(sin($rad));

        $w = (float) $p['w_mm'] * $cos + (float) $p['h_mm'] * $sin;
        $h = (float) $p['w_mm'] * $sin + (float) $p['h_mm'] * $cos;

        $cx = (float) $p['x_mm'] + (float) $p['w_mm'] / 2;
        $cy = (float) $p['y_mm'] + (float) $p['h_mm'] / 2;

        return ['x' => $cx - $w / 2, 'y' => $cy - $h / 2, 'w' => $w, 'h' => $h];
    }

    /**
     * Khung bao chung của mọi hình trong CÙNG một vị trí.
     *
     * Đây là chỗ quyết định cách tính tiền: gộp lại rồi mới quy ra khổ, nên ba
     * sticker nhỏ nằm gọn trong A5 tính tiền A5 chứ không phải ba lần tiền.
     * Vừa đúng chi phí thật của xưởng, vừa giải thích được cho khách.
     */
    public static function boundingBox(array $placements): array
    {
        $x0 = INF;
        $y0 = INF;
        $x1 = -INF;
        $y1 = -INF;

        foreach ($placements as $p) {
            $b = self::placementBox($p);
            $x0 = min($x0, $b['x']);
            $y0 = min($y0, $b['y']);
            $x1 = max($x1, $b['x'] + $b['w']);
            $y1 = max($y1, $b['y'] + $b['h']);
        }

        return ['x' => $x0, 'y' => $y0, 'w' => $x1 - $x0, 'h' => $y1 - $y0];
    }

    /**
     * Bậc khổ NHỎ NHẤT chứa được khung bao; null nếu vượt bậc lớn nhất.
     *
     * Cho phép xoay 90°: khung 200×140 vừa khổ A5 dựng đứng 148×210 nếu quay
     * ngang, và xưởng in vẫn cắt được từ đúng tờ decal đó.
     */
    public static function pickTier(array $bbox, array $tiers): ?array
    {
        $sorted = $tiers;
        usort($sorted, fn ($a, $b) => ($a['width_mm'] * $a['height_mm']) <=> ($b['width_mm'] * $b['height_mm']));

        foreach ($sorted as $tier) {
            $fitsUpright = $bbox['w'] <= $tier['width_mm'] + 0.5 && $bbox['h'] <= $tier['height_mm'] + 0.5;
            $fitsTurned = $bbox['w'] <= $tier['height_mm'] + 0.5 && $bbox['h'] <= $tier['width_mm'] + 0.5;

            if ($fitsUpright || $fitsTurned) {
                return $tier;
            }
        }

        return null;
    }

    // ── Ngữ pháp quy tắc ─────────────────────────────────────────────

    /**
     * Mọi điều kiện đều AND với nhau; điều kiện vắng mặt = không xét.
     *
     * Danh sách điều kiện ở đây là ĐÓNG. Thêm một loại mới là việc của lập
     * trình viên và phải sửa cả bản TypeScript bên web — cho sửa tự do trong
     * giao diện là quay về đúng cái bẫy trình soạn công thức.
     */
    public static function ruleMatches(array $rule, array $ctx): bool
    {
        $when = (array) ($rule['when'] ?? []);

        if (isset($when['technique_ids']) && !in_array($ctx['technique_id'], (array) $when['technique_ids'])) {
            return false;
        }
        // `zone_keys` là tên cũ, còn nằm trong các ảnh chụp bảng giá đã xuất bản.
        $positionKeys = $when['position_keys'] ?? $when['zone_keys'] ?? null;
        if ($positionKeys !== null) {
            if ($ctx['position_key'] === null || !in_array($ctx['position_key'], (array) $positionKeys, true)) {
                return false;
            }
        }
        if (isset($when['tier_ids'])) {
            if ($ctx['tier_id'] === null || !in_array($ctx['tier_id'], (array) $when['tier_ids'])) {
                return false;
            }
        }
        if (isset($when['tone']) && $when['tone'] !== $ctx['tone']) {
            return false;
        }
        if (isset($when['blank_ids']) && !in_array($ctx['blank_id'], (array) $when['blank_ids'])) {
            return false;
        }
        if (isset($when['qty_from']) && $ctx['qty'] < (int) $when['qty_from']) {
            return false;
        }
        if (isset($when['qty_to']) && $ctx['qty'] > (int) $when['qty_to']) {
            return false;
        }
        if (isset($when['ink_colors_from']) && $ctx['ink_colors'] < (int) $when['ink_colors_from']) {
            return false;
        }

        return true;
    }

    // ── Báo giá ──────────────────────────────────────────────────────

    /**
     * Tính tiền một thiết kế.
     *
     * @param array $design [
     *     'blank' => ['id','name','base_price','moq','product_id'],
     *     'size', 'size_surcharge', 'color_name', 'tone',
     *     'technique_id', 'ink_colors', 'qty',
     *     'positions' => [position_key => ['label','max_width_mm','max_height_mm']],
     *     'placements' => [['position','x_mm','y_mm','w_mm','h_mm','rotation','asset_fee','asset_name']],
     * ]
     * @param array|null $pricing Ảnh chụp bảng giá; null = bản đang có hiệu lực.
     *
     * @return array ['lines','unit_price','total','errors','warnings']
     */
    public static function quote(array $design, ?array $pricing = null): array
    {
        $pricing = $pricing ?? self::current();

        if (($pricing['mode'] ?? null) === self::MODE_SIMPLE) {
            return self::quoteSimple($design, $pricing);
        }

        $lines = [];
        $errors = [];
        $warnings = [];

        $blank = (array) $design['blank'];
        $qty = max(1, (int) ($design['qty'] ?? 1));
        $inkColors = max(1, (int) ($design['ink_colors'] ?? 1));
        $tone = $design['tone'] ?? 'light';
        $techniqueId = $design['technique_id'] ?? null;

        $technique = collect($pricing['techniques'] ?? [])->firstWhere('id', $techniqueId);
        if (!$technique) {
            return [
                'lines' => [],
                'unit_price' => 0,
                'total' => 0,
                'errors' => ['Kỹ thuật in này không còn trong bảng giá đang áp dụng.'],
                'warnings' => [],
            ];
        }

        $tiers = (array) ($pricing['tiers'] ?? []);
        $cells = (array) ($pricing['cells'] ?? []);
        $rules = (array) ($pricing['rules'] ?? []);

        // ── BƯỚC 1 — giá phôi ────────────────────────────────────────
        $surcharge = (int) ($design['size_surcharge'] ?? 0);
        $running = (float) $blank['base_price'] + $surcharge;

        $lines[] = self::line(
            $blank['name'] . ' — ' . ($design['color_name'] ?? '') . ', size ' . ($design['size'] ?? ''),
            (float) $blank['base_price'],
            empty($blank['product_id']) ? 'phôi đứng riêng' : 'nối kho · sản phẩm #' . $blank['product_id'],
        );

        if ($surcharge) {
            $lines[] = self::line('phụ thu size ' . $design['size'], $surcharge, null, true);
        }

        // ── BƯỚC 2 — giá in cơ bản, tính riêng từng vị trí ───────────
        $byPosition = [];
        foreach ((array) ($design['placements'] ?? []) as $p) {
            // `zone` là tên cũ của trường này trong các thiết kế lưu trước đây.
            $byPosition[$p['position'] ?? $p['zone'] ?? ''][] = $p;
        }

        $positionContexts = [];
        foreach ($byPosition as $positionKey => $placements) {
            $position = ($design['positions'] ?? [])[$positionKey] ?? null;

            /*
             * Bỏ qua trong im lặng là hình của khách biến mất khỏi bảng kê mà
             * vẫn nằm trong thiết kế đã lưu — khách trả tiền một đằng, xưởng in
             * một nẻo. Nói thẳng ra và chặn đơn lại.
             */
            if (!$position) {
                $errors[] = sprintf('Vị trí in "%s" không còn nhận đơn trên phôi này.', $positionKey);
                continue;
            }

            $bbox = self::boundingBox($placements);

            /*
             * Trần mm của vị trí — giới hạn của cái máy in, KHÔNG phải khung in
             * cũ quay lại dưới tên khác: bên trong trần đó khách vẫn đặt hình ở
             * đâu và to nhỏ thế nào tuỳ ý. Chặn ngay tại đây rẻ hơn để xưởng
             * nhận đơn rồi gọi điện từ chối.
             */
            $maxW = (float) ($position['max_width_mm'] ?? INF);
            $maxH = (float) ($position['max_height_mm'] ?? INF);

            if ($bbox['w'] > $maxW + 0.5 || $bbox['h'] > $maxH + 0.5) {
                $errors[] = sprintf(
                    '%s: khung bao %s×%s mm vượt giới hạn %s×%s mm của vị trí này.',
                    $position['label'],
                    round($bbox['w'], 1),
                    round($bbox['h'], 1),
                    round($maxW),
                    round($maxH),
                );
                continue;
            }

            $tier = self::pickTier($bbox, $tiers);

            if (!$tier) {
                $errors[] = sprintf(
                    '%s: khung bao %s×%s mm vượt bậc khổ lớn nhất.',
                    $position['label'],
                    round($bbox['w'], 1),
                    round($bbox['h'], 1),
                );
                continue;
            }

            $cell = $cells[$technique['id']][$tier['id']] ?? null;
            if ($cell === null) {
                $errors[] = sprintf(
                    '%s không nhận khổ %s — đổi kỹ thuật hoặc thu nhỏ hình ở %s.',
                    $technique['name'],
                    $tier['name'],
                    $position['label'],
                );
                continue;
            }

            $lines[] = self::line(
                sprintf('%s · %s · khổ %s', $technique['name'], $position['label'], $tier['name']),
                (float) $cell,
                sprintf('khung bao %s × %s mm · %d hình', round($bbox['w'], 1), round($bbox['h'], 1), count($placements)),
            );
            $running += (float) $cell;

            $positionContexts[] = [
                'position_key' => $positionKey,
                'position_label' => $position['label'],
                'tier_id' => $tier['id'],
                'base' => (float) $cell,
                'count' => count($placements),
            ];
        }

        // Sticker có bản quyền — phí gắn với tài nguyên, không gắn với vị trí.
        foreach ((array) ($design['placements'] ?? []) as $p) {
            $fee = (int) ($p['asset_fee'] ?? 0);
            if ($fee > 0) {
                $lines[] = self::line('Sticker "' . ($p['asset_name'] ?? '') . '"', $fee, 'có tính phí', true);
                $running += $fee;
            }
        }

        if (!$positionContexts && !$errors) {
            $warnings[] = 'Chưa có hình nào trên áo — mới tính tiền phôi.';
        }

        $baseCtx = [
            'technique_id' => $technique['id'],
            'blank_id' => $blank['id'] ?? null,
            'tone' => $tone,
            'qty' => $qty,
            'ink_colors' => $inkColors,
        ];

        // ── BƯỚC 3 — phụ phí CỘNG ────────────────────────────────────
        foreach ($rules as $rule) {
            if (empty($rule['enabled']) || ($rule['apply']['kind'] ?? '') !== self::KIND_ADD) {
                continue;
            }

            $per = self::normalisePer($rule['apply']['per'] ?? null);
            $amount = (float) ($rule['apply']['amount'] ?? 0);

            if (in_array($per, self::PER_POSITION_SCOPED, true)) {
                foreach ($positionContexts as $pc) {
                    $ctx = $baseCtx + ['position_key' => $pc['position_key'], 'tier_id' => $pc['tier_id']];
                    if (!self::ruleMatches($rule, $ctx)) {
                        continue;
                    }

                    [$multiplier, $note] = self::additiveMultiplier($rule, $per, $pc, $inkColors);
                    if ($multiplier <= 0) {
                        continue;
                    }

                    $delta = $amount * $multiplier;
                    $lines[] = self::line($rule['label'] . ' — ' . $pc['position_label'], $delta, $note, true);
                    $running += $delta;
                }
                continue;
            }

            $ctx = $baseCtx + ['position_key' => null, 'tier_id' => null];
            if (!self::ruleMatches($rule, $ctx)) {
                continue;
            }

            /*
             * `mỗi đơn` phải chia đều cho số áo TRƯỚC khi cộng.
             *
             * Toàn bộ bộ máy này tính GIÁ MỘT ÁO, và tổng đơn là đơn giá nhân số
             * lượng — bất biến mà giỏ hàng, PayOS và hoá đơn đều dựa vào. Cộng
             * thẳng một khoản "mỗi đơn" vào đơn giá là nó bị nhân lên theo số áo:
             * phụ phí 30.000 cho đơn 4 áo hoá thành 120.000.
             */
            $share = $per === self::PER_ORDER ? $amount / $qty : $amount;
            $note = $per === self::PER_ORDER && $qty > 1
                ? sprintf('%s ₫ chia đều %d áo', number_format($amount, 0, ',', '.'), $qty)
                : (self::PER_LABELS[$per] ?? null);

            $lines[] = self::line($rule['label'], $share, $note, true);
            $running += $share;
        }

        // ── BƯỚC 4 — hệ số NHÂN ──────────────────────────────────────
        foreach ($rules as $rule) {
            if (empty($rule['enabled'])) {
                continue;
            }

            $kind = $rule['apply']['kind'] ?? '';
            if ($kind !== self::KIND_MULTIPLY && $kind !== self::KIND_PERCENT) {
                continue;
            }

            $per = self::normalisePer($rule['apply']['per'] ?? null);
            $amount = (float) ($rule['apply']['amount'] ?? 0);
            $factor = $kind === self::KIND_MULTIPLY ? $amount : 1 + $amount / 100;
            $note = $kind === self::KIND_MULTIPLY ? '×' . $amount : '+' . $amount . '%';

            if (in_array($per, self::PER_POSITION_SCOPED, true)) {
                foreach ($positionContexts as $pc) {
                    $ctx = $baseCtx + ['position_key' => $pc['position_key'], 'tier_id' => $pc['tier_id']];
                    if (!self::ruleMatches($rule, $ctx)) {
                        continue;
                    }

                    // Nhân trên GIÁ IN CƠ BẢN của vị trí, không trên tổng đang
                    // chạy: "mặt sau khó căn hơn" nói về công in mặt sau, chứ
                    // không về tiền phôi hay phụ phí của vị trí khác.
                    $delta = $pc['base'] * ($factor - 1);
                    $lines[] = self::line($rule['label'] . ' — ' . $pc['position_label'], $delta, $note, true);
                    $running += $delta;
                }
                continue;
            }

            $ctx = $baseCtx + ['position_key' => null, 'tier_id' => null];
            if (!self::ruleMatches($rule, $ctx)) {
                continue;
            }

            $delta = $running * ($factor - 1);
            $lines[] = self::line($rule['label'], $delta, $note . ' trên toàn đơn', true);
            $running += $delta;
        }

        // ── BƯỚC 5 — chiết khấu số lượng ─────────────────────────────
        $qtyTier = collect((array) ($pricing['qty_tiers'] ?? []))
            ->filter(fn ($q) => $qty >= (int) $q['from'])
            ->sortByDesc('from')
            ->first();

        if ($qtyTier && (float) $qtyTier['pct'] > 0) {
            $delta = -$running * ((float) $qtyTier['pct'] / 100);
            $lines[] = self::line(
                'Chiết khấu từ ' . $qtyTier['from'] . ' áo',
                $delta,
                '−' . $qtyTier['pct'] . '%',
            );
            $running += $delta;
        }

        // ── BƯỚC 6 — sàn giá rồi làm tròn ────────────────────────────
        $minCharge = (int) ($pricing['min_charge'] ?? 0);
        if ($minCharge > 0 && $running < $minCharge) {
            $lines[] = self::line('Nâng lên sàn giá mỗi áo', $minCharge - $running);
            $running = (float) $minCharge;
        }

        $rounding = (int) ($pricing['rounding'] ?? 0);
        if ($rounding > 0) {
            $rounded = (float) (ceil($running / $rounding) * $rounding);
            if (abs($rounded - $running) > 0.001) {
                $lines[] = self::line(
                    'Làm tròn lên bội số ' . number_format($rounding, 0, ',', '.') . ' ₫',
                    $rounded - $running,
                    null,
                    true,
                );
            }
            $running = $rounded;
        }

        $unitPrice = (int) round($running);

        // ── Ràng buộc của kỹ thuật — đọc từ dữ liệu, không if theo tên ──
        if ($technique['max_colors'] !== null && $inkColors > (int) $technique['max_colors']) {
            $errors[] = sprintf(
                '%s chỉ in tối đa %d màu, đang khai %d.',
                $technique['name'],
                $technique['max_colors'],
                $inkColors,
            );
        }
        if ($qty < (int) $technique['moq']) {
            $warnings[] = sprintf('%s nhận đơn tối thiểu %d áo.', $technique['name'], $technique['moq']);
        }
        if ($qty < (int) ($blank['moq'] ?? 1)) {
            $warnings[] = sprintf('Phôi "%s" nhận đơn tối thiểu %d áo.', $blank['name'], $blank['moq']);
        }

        return [
            'lines' => $lines,
            'unit_price' => $unitPrice,
            'total' => $unitPrice * $qty,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Báo giá gọn: giá phôi + một phí in cố định theo kỹ thuật.
     *
     * Vị trí và khung mm vẫn được kiểm tra để giữ an toàn cho xưởng, nhưng không
     * còn ảnh hưởng tới tiền. Một thiết kế có cả mặt trước và mặt sau vẫn chỉ
     * dùng đúng mức phí của cặp phôi + kỹ thuật đã chọn.
     */
    private static function quoteSimple(array $design, array $pricing): array
    {
        $lines = [];
        $errors = [];
        $warnings = [];

        $blank = (array) ($design['blank'] ?? []);
        $blankId = (int) ($blank['id'] ?? 0);
        $qty = max(1, (int) ($design['qty'] ?? 1));
        $inkColors = max(1, (int) ($design['ink_colors'] ?? 1));
        $techniqueId = (int) ($design['technique_id'] ?? 0);
        $technique = collect($pricing['techniques'] ?? [])->firstWhere('id', $techniqueId);

        if (!$technique) {
            return [
                'lines' => [],
                'unit_price' => 0,
                'total' => 0,
                'errors' => ['Kỹ thuật in này không còn trong bảng giá đang áp dụng.'],
                'warnings' => [],
            ];
        }

        $basePrice = max(0, (int) ($blank['base_price'] ?? 0));
        $printPrice = self::simplePriceFor($pricing, $blankId, $techniqueId);
        $color = trim((string) ($design['color_name'] ?? ''));
        $blankLabel = trim((string) ($blank['name'] ?? 'Phôi')) . ($color !== '' ? ' — ' . $color : '');

        $lines[] = self::line(
            $blankLabel,
            $basePrice,
            empty($blank['product_id']) ? 'giá phôi' : 'giá lấy từ sản phẩm trong kho',
        );

        if ($printPrice === null) {
            $errors[] = sprintf('Phôi "%s" chưa có giá cho kỹ thuật "%s".', $blank['name'] ?? 'này', $technique['name']);
        } else {
            $lines[] = self::line($technique['name'] . ' · giá cố định / áo', $printPrice, 'không tính theo kích thước');
        }

        $positions = (array) ($design['positions'] ?? []);
        $byPosition = [];
        foreach ((array) ($design['placements'] ?? []) as $placement) {
            $key = $placement['position'] ?? $placement['zone'] ?? '';
            $byPosition[$key][] = $placement;
        }

        foreach ($byPosition as $positionKey => $placements) {
            $position = $positions[$positionKey] ?? null;
            if (!$position) {
                $errors[] = sprintf('Vị trí in "%s" không còn nhận đơn trên phôi này.', $positionKey);
                continue;
            }

            $bbox = self::boundingBox($placements);
            $maxW = (float) ($position['max_width_mm'] ?? INF);
            $maxH = (float) ($position['max_height_mm'] ?? INF);
            if ($bbox['w'] > $maxW + 0.5 || $bbox['h'] > $maxH + 0.5) {
                $errors[] = sprintf(
                    '%s: hình vượt giới hạn %s×%s mm của vị trí này.',
                    $position['label'] ?? $positionKey,
                    round($maxW),
                    round($maxH),
                );
            }
        }

        if (!$byPosition) {
            $warnings[] = 'Chưa có hình nào trên áo — giá vẫn giữ nguyên theo phôi và kỹ thuật.';
        }

        if ($technique['max_colors'] !== null && $inkColors > (int) $technique['max_colors']) {
            $errors[] = sprintf(
                '%s chỉ in tối đa %d màu, đang khai %d.',
                $technique['name'],
                $technique['max_colors'],
                $inkColors,
            );
        }
        if ($qty < (int) ($technique['moq'] ?? 1)) {
            $warnings[] = sprintf('%s nhận đơn tối thiểu %d áo.', $technique['name'], $technique['moq']);
        }
        if ($qty < (int) ($blank['moq'] ?? 1)) {
            $warnings[] = sprintf('Phôi "%s" nhận đơn tối thiểu %d áo.', $blank['name'] ?? 'này', $blank['moq']);
        }

        $unitPrice = $printPrice === null ? 0 : $basePrice + $printPrice;

        return [
            'lines' => $lines,
            'unit_price' => $unitPrice,
            'total' => $unitPrice * $qty,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Hệ số nhân của một quy tắc cộng, tuỳ theo nó tính trên đơn vị nào.
     *
     * `mỗi màu mực` đếm từ ngưỡng của chính quy tắc trở đi: quy tắc "từ màu thứ
     * 2" với đơn 4 màu thì tính tiền 3 màu, không phải 4.
     */
    private static function additiveMultiplier(array $rule, string $per, array $positionCtx, int $inkColors): array
    {
        if ($per === self::PER_PLACEMENT) {
            return [$positionCtx['count'], $positionCtx['count'] . ' hình'];
        }

        if ($per === self::PER_INK_COLOR) {
            $from = (int) ($rule['when']['ink_colors_from'] ?? 1);
            $billable = max(0, $inkColors - ($from - 1));

            return [$billable, $billable . ' màu tính phí'];
        }

        return [1, null];
    }

    /** Một dòng của bảng kê. `sub` = dòng phụ, thụt vào dưới dòng cha. */
    private static function line(string $label, float $amount, ?string $meta = null, bool $sub = false): array
    {
        return [
            'label' => $label,
            'meta' => $meta,
            'amount' => (int) round($amount),
            'sub' => $sub,
        ];
    }
}
