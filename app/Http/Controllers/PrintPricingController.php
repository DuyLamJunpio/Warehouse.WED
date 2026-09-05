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
            'blanks' => PrintBlank::with('techniques')->orderBy('sort_order')->orderBy('id')->get(),
            'simplePrices' => PrintPricing::resolvedBlankTechniquePrices(),
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
            'mode' => 'sometimes|in:simple',
            'blank_technique_prices' => 'present|array',
            'blank_technique_prices.*' => 'array',
            'blank_technique_prices.*.*' => 'nullable|integer|min:0|max:100000000',
            // Các trường cũ vẫn nhận được để một tab bảng giá cũ không làm
            // hỏng lần lưu sau khi cập nhật giao diện.
            'cells' => 'sometimes|array',
        ]);

        $validPairs = [];
        foreach (PrintBlank::with('techniques')->get() as $blank) {
            $validPairs[(string) $blank->id] = $blank->techniques->pluck('id')->map(fn ($id) => (string) $id)->all();
        }

        $simplePrices = [];
        foreach ((array) $data['blank_technique_prices'] as $blankId => $row) {
            $blankKey = (string) $blankId;
            if (!array_key_exists($blankKey, $validPairs)) {
                continue;
            }

            foreach ((array) $row as $techniqueId => $price) {
                $techniqueKey = (string) $techniqueId;
                if (!in_array($techniqueKey, $validPairs[$blankKey], true)) {
                    continue;
                }

                // null là chủ động bỏ giá, khác với việc không có cặp này trong
                // form. Nó ngăn giá cũ theo kích thước tự quay lại.
                $simplePrices[$blankKey][$techniqueKey] = $price === null
                    ? null
                    : max(0, (int) $price);
            }
        }

        $draft = PrintPricing::draft();
        PrintPricing::putDraft([
            ...$draft,
            'mode' => PrintPricing::MODE_SIMPLE,
            'blank_technique_prices' => $simplePrices,
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
            'qty' => 'required|integer|min:1|max:10000',
        ]);

        $blank = PrintBlank::with('product.variants')->findOrFail($data['blank_id']);
        if (!$blank->techniques()->whereKey($data['technique_id'])->exists()) {
            return response()->json([
                'error' => 'Kỹ thuật này chưa được bật cho phôi đã chọn.',
            ], 422);
        }

        $positionKey = $blank->positionKeys()[0] ?? PrintPositions::FRONT;
        $position = PrintPositions::get($positionKey);

        $quote = PrintPricing::quote([
            'blank' => [
                'id' => $blank->id,
                'name' => $blank->name,
                'base_price' => $blank->effectiveBasePrice(),
                'moq' => $blank->moq,
                'product_id' => $blank->product_id,
            ],
            'size' => 'Một cỡ',
            'size_surcharge' => 0,
            'color_name' => '',
            'tone' => 'light',
            'technique_id' => (int) $data['technique_id'],
            'ink_colors' => (int) ($data['ink_colors'] ?? 1),
            'qty' => (int) $data['qty'],
            'positions' => PrintPositions::pricingMap([$positionKey]),
            'placements' => [[
                'position' => $positionKey,
                'x_mm' => 0, 'y_mm' => 0,
                'w_mm' => min(100, $position['max_width_mm']),
                'h_mm' => min(100, $position['max_height_mm']),
                'rotation' => 0,
            ]],
        ], PrintPricing::snapshot());

        return response()->json($quote);
    }
}
