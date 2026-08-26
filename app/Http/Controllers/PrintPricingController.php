<?php

namespace App\Http\Controllers;

use App\Models\PrintBlank;
use App\Models\PrintPricingVersion;
use App\Models\PrintSizeTier;
use App\Models\PrintTechnique;
use App\Services\PrintPositions;
use App\Services\PrintPricing;
use App\Services\StorefrontNotifier;
use Illuminate\Http\Request;

/**
 * Màn hình bảng giá in.
 *
 * Chủ shop sửa vào BẢN NHÁP; web bán hàng vẫn báo giá theo bản đã xuất bản cho
 * tới khi bấm "Xuất bản". Nhờ vậy sửa nửa chừng rồi đi ăn trưa không làm khách
 * nhìn thấy một bảng giá dở dang, và gõ nhầm một số 0 thì lùi lại được.
 */
class PrintPricingController extends Controller
{
    public function __construct(private StorefrontNotifier $notifier)
    {
    }

    public function index()
    {
        return view('print.pricing', [
            'draft' => PrintPricing::draft(),
            'techniques' => PrintTechnique::orderBy('sort_order')->orderBy('id')->get(),
            'tiers' => PrintSizeTier::orderBy('sort_order')->orderBy('id')->get(),
            'blanks' => PrintBlank::orderBy('sort_order')->get(),
            'positions' => PrintPositions::payload(),
            'versions' => PrintPricingVersion::with('publisher')->orderByDesc('id')->limit(10)->get(),
            'currentVersion' => PrintPricingVersion::latestPublished(),
            'perLabels' => PrintPricing::PER_LABELS,
        ]);
    }

    /**
     * Lưu bản nháp.
     *
     * Ô giá để trống KHÁC ô giá 0: trống nghĩa là kỹ thuật đó không nhận khổ đó
     * và studio phải ẩn lựa chọn, còn 0 nghĩa là in miễn phí. Chuỗi rỗng từ form
     * phải rơi về null chứ không được ép về (int) 0.
     */
    public function saveDraft(Request $request)
    {
        $data = $request->validate([
            'cells' => 'present|array',
            'rules' => 'present|array',
            'rules.*.id' => 'required|string|max:60',
            'rules.*.label' => 'required|string|max:120',
            'rules.*.enabled' => 'nullable|boolean',
            'rules.*.apply.kind' => 'required|in:add,multiply,percent',
            'rules.*.apply.amount' => 'required|numeric|min:0',
            // `zone` là tên cũ của `position`; nhận vào rồi quy về tên mới ngay
            // bên dưới, để một tab trình duyệt mở từ trước không làm hỏng lần lưu.
            'rules.*.apply.per' => 'required|in:order,shirt,position,placement,inkColor,zone',
            'qty_tiers' => 'present|array',
            'qty_tiers.*.from' => 'required|integer|min:1',
            'qty_tiers.*.pct' => 'required|numeric|min:0|max:100',
            'rounding' => 'required|integer|min:0',
            'min_charge' => 'required|integer|min:0',
        ]);

        $cells = [];
        foreach ($data['cells'] as $techniqueId => $row) {
            foreach ((array) $row as $tierId => $price) {
                if ($price === null || $price === '') {
                    continue;
                }
                $cells[(int) $techniqueId][(int) $tierId] = max(0, (int) $price);
            }
        }

        $rules = array_map(function (array $rule) {
            return [
                'id' => $rule['id'],
                'label' => $rule['label'],
                'enabled' => (bool) ($rule['enabled'] ?? false),
                // `when` đi thẳng qua: ngữ pháp điều kiện do seeder và mã nguồn
                // dựng, giao diện chỉ bật/tắt và đổi số tiền. Cho form ghi đè
                // phần này là mở lại đúng cái bẫy trình soạn công thức.
                'when' => (array) ($rule['when'] ?? []),
                'apply' => [
                    'kind' => $rule['apply']['kind'],
                    'amount' => (float) $rule['apply']['amount'],
                    'per' => PrintPricing::normalisePer($rule['apply']['per']),
                ],
            ];
        }, $data['rules']);

        $qtyTiers = collect($data['qty_tiers'])
            ->map(fn ($q) => ['from' => (int) $q['from'], 'pct' => (float) $q['pct']])
            ->sortBy('from')
            ->values()
            ->all();

        PrintPricing::putDraft([
            'cells' => $cells,
            'rules' => $rules,
            'qty_tiers' => $qtyTiers,
            'rounding' => (int) $data['rounding'],
            'min_charge' => (int) $data['min_charge'],
        ]);

        return response()->json([
            'success' => 'Đã lưu bản nháp. Bấm "Xuất bản" thì khách mới thấy giá mới.',
        ]);
    }

    /** Đóng băng bản nháp thành phiên bản mới và báo cho web bán hàng. */
    public function publish(Request $request)
    {
        $data = $request->validate(['note' => 'nullable|string|max:255']);

        $version = PrintPricing::publish($data['note'] ?? null, $request->user()?->id);
        $this->notifier->markDirty();

        return response()->json([
            'success' => 'Đã xuất bản bảng giá #' . $version->id . '. Đơn cũ giữ nguyên giá đã chốt.',
            'version_id' => $version->id,
        ]);
    }

    /**
     * Thử giá ngay trong màn hình cài đặt.
     *
     * Tính trên BẢN NHÁP chứ không phải bản đã xuất bản — cả điểm của nó là xem
     * con số vừa gõ ra bao nhiêu TRƯỚC khi phát hành cho khách.
     */
    public function simulate(Request $request)
    {
        $data = $request->validate([
            'blank_id' => 'required|exists:print_blanks,id',
            'technique_id' => 'required|exists:print_techniques,id',
            'position_key' => 'required|string|in:' . implode(',', PrintPositions::keys()),
            'tier_id' => 'required|exists:print_size_tiers,id',
            'tone' => 'required|in:light,dark',
            'qty' => 'required|integer|min:1|max:10000',
            'ink_colors' => 'nullable|integer|min:1',
        ]);

        $blank = PrintBlank::with('product.variants')->findOrFail($data['blank_id']);
        $tier = PrintSizeTier::findOrFail($data['tier_id']);

        $positionKey = $data['position_key'];
        $position = PrintPositions::get($positionKey);

        if (!in_array($positionKey, $blank->positionKeys(), true)) {
            return response()->json([
                'error' => 'Phôi này đang tắt vị trí "' . PrintPositions::label($positionKey) . '".',
            ], 422);
        }

        // Dựng một hình vừa khít bậc khổ đã chọn — đúng thứ cần để đọc ra giá
        // của ô đó trong ma trận, không hơn. Cắt theo trần của vị trí, nếu không
        // thì thử giá một khổ mà khách không đặt nổi ở chỗ đó.
        $width = min($tier->width_mm, $position['max_width_mm']);
        $height = min($tier->height_mm, $position['max_height_mm']);

        $quote = PrintPricing::quote([
            'blank' => [
                'id' => $blank->id,
                'name' => $blank->name,
                'base_price' => $blank->effectiveBasePrice(),
                'moq' => $blank->moq,
                'product_id' => $blank->product_id,
            ],
            'size' => 'L',
            'size_surcharge' => 0,
            'color_name' => $data['tone'] === 'dark' ? 'Màu tối' : 'Màu sáng',
            'tone' => $data['tone'],
            'technique_id' => (int) $data['technique_id'],
            'ink_colors' => (int) ($data['ink_colors'] ?? 1),
            'qty' => (int) $data['qty'],
            'positions' => PrintPositions::pricingMap([$positionKey]),
            'placements' => [[
                'position' => $positionKey,
                'x_mm' => 0, 'y_mm' => 0,
                'w_mm' => $width, 'h_mm' => $height,
                'rotation' => 0,
            ]],
        ], PrintPricing::snapshot());

        return response()->json($quote);
    }
}
