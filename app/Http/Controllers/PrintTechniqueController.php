<?php

namespace App\Http\Controllers;

use App\Models\PrintBlank;
use App\Models\PrintAsset;
use App\Models\PrintDesign;
use App\Models\PrintSizeTier;
use App\Models\PrintTechnique;
use App\Services\PrintPricing;
use App\Services\StorefrontNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Kỹ thuật in và bậc khổ — hai thứ chủ shop tự tạo được.
 *
 * Ràng buộc của kỹ thuật là DỮ LIỆU: số màu tối đa, có nhận ảnh chụp không, DPI
 * tối thiểu. Studio bên web đọc đúng mấy trường đó để chặn khách, nên tạo thêm
 * một kỹ thuật lạ không cần ai sửa code.
 *
 * Có thể tắt để ẩn khỏi web, hoặc xoá hẳn bản ghi chưa được thiết kế khách sử
 * dụng. Đơn cũ vẫn an toàn vì bản giá đã xuất bản là snapshot bất biến.
 */
class PrintTechniqueController extends Controller
{
    public function __construct(private StorefrontNotifier $notifier)
    {
    }

    public function index()
    {
        $techniques = PrintTechnique::orderBy('sort_order')->orderBy('id')->get();
        $draft = PrintPricing::draft();
        $simplePrices = PrintPricing::resolvedBlankTechniquePrices($draft);

        // Đếm tham chiếu treo: tắt một kỹ thuật thì nói ngay bao nhiêu phôi và
        // quy tắc đang dùng nó. Không chặn, chỉ nói.
        $usage = [];
        foreach ($techniques as $technique) {
            $usage[$technique->id] = [
                'blanks' => PrintBlank::whereHas('techniques', fn ($q) => $q->where('print_techniques.id', $technique->id))->count(),
                'designs' => PrintDesign::where('print_technique_id', $technique->id)->count(),
                'rules' => collect($draft['rules'] ?? [])
                    ->filter(fn ($r) => in_array($technique->id, (array) ($r['when']['technique_ids'] ?? [])))
                    ->count(),
                'priced' => collect($simplePrices)
                    ->filter(fn ($row) => array_key_exists((string) $technique->id, (array) $row)
                        && $row[(string) $technique->id] !== null)
                    ->count(),
            ];
        }

        return view('print.techniques', [
            'techniques' => $techniques,
            'tiers' => PrintSizeTier::orderBy('sort_order')->orderBy('id')->get(),
            'usage' => $usage,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['slug'] = Str::slug($data['name']) ?: 'ky-thuat-' . Str::random(6);
        if (PrintTechnique::where('slug', $data['slug'])->exists()) {
            $data['slug'] .= '-' . Str::lower(Str::random(4));
        }
        $data['sort_order'] = (int) PrintTechnique::max('sort_order') + 1;
        $data['is_active'] = true;

        $technique = PrintTechnique::create($data);
        $this->notifier->markDirty();

        return response()->json([
            'success' => 'Đã tạo "' . $technique->name . '". Điền giá cho nó trong ma trận thì khách mới chọn được.',
            'id' => $technique->id,
        ]);
    }

    public function update(Request $request, PrintTechnique $technique)
    {
        $technique->update($this->validated($request));
        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã lưu "' . $technique->name . '".']);
    }

    /**
     * Xoá hẳn một kỹ thuật chưa được dùng trong thiết kế khách.
     *
     * Kỹ thuật đang có thiết kế phải được tắt thay vì xoá để khoá ngoại và
     * phần đọc đơn cũ vẫn còn tên kỹ thuật. Các tham chiếu ở bản nháp được dọn
     * trong cùng giao dịch, còn bản giá đã xuất bản không đụng tới.
     */
    public function destroy(PrintTechnique $technique)
    {
        $designs = PrintDesign::where('print_technique_id', $technique->id)->count();

        if ($designs > 0) {
            return response()->json([
                'error' => 'Không xoá được kỹ thuật "' . $technique->name . '": đang có ' . $designs
                    . ' thiết kế của khách trỏ vào nó. Hãy tắt kỹ thuật để ẩn khỏi web — dữ liệu cũ giữ nguyên.',
            ], 422);
        }

        $name = $technique->name;

        DB::transaction(function () use ($technique) {
            $technique->blanks()->detach();
            $this->removeTechniqueFromAssets($technique->id);
            $this->removeFromPricingDraft('technique_ids', $technique->id);
            $technique->delete();
        });

        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã xoá kỹ thuật "' . $name . '".']);
    }

    /** Bật/tắt để ẩn kỹ thuật khỏi web mà vẫn giữ nguyên dữ liệu cũ. */
    public function toggle(Request $request, PrintTechnique $technique)
    {
        $technique->update(['is_active' => $request->boolean('is_active')]);
        $this->notifier->markDirty();

        return response()->json([
            'success' => $technique->is_active
                ? 'Đã bật "' . $technique->name . '".'
                : 'Đã tắt "' . $technique->name . '" — ẩn khỏi web, dữ liệu cũ giữ nguyên.',
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            // Chuỗi rỗng = không giới hạn màu, khác hẳn với giới hạn 0 màu.
            'max_colors' => 'nullable|integer|min:1|max:99',
            'accepts_photo' => 'nullable|boolean',
            'accepts_gradient' => 'nullable|boolean',
            'needs_underbase' => 'nullable|boolean',
            'min_dpi' => 'required|integer|min:30|max:1200',
            'file_types' => 'required|string|max:255',
            'lead_days' => 'required|integer|min:0|max:365',
            'moq' => 'required|integer|min:1|max:9999',
        ]);
    }

    // ── Bậc khổ in ───────────────────────────────────────────────────

    public function storeTier(Request $request)
    {
        $data = $this->validatedTier($request);
        $data['sort_order'] = (int) PrintSizeTier::max('sort_order') + 1;
        $data['is_active'] = true;

        $tier = PrintSizeTier::create($data);
        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã thêm bậc khổ ' . $tier->name . '.', 'id' => $tier->id]);
    }

    /**
     * Sửa một bậc khổ.
     *
     * Sửa kích thước KHÔNG đụng tới đơn cũ: mỗi thiết kế đã chốt mang theo ảnh
     * chụp bảng giá của chính nó, trong đó có kích thước bậc khổ tại thời điểm
     * đặt. Thay đổi ở đây chỉ có hiệu lực từ lần xuất bản kế tiếp.
     */
    public function updateTier(Request $request, PrintSizeTier $tier)
    {
        $tier->update($this->validatedTier($request));
        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã lưu bậc khổ ' . $tier->name . '.']);
    }

    /**
     * Xoá hẳn một bậc khổ.
     *
     * Bản giá đã xuất bản chụp riêng kích thước bậc khổ nên không bị ảnh hưởng.
     * Chỉ bản nháp cần bỏ ô giá và vô hiệu hoá quy tắc còn trỏ vào bậc này.
     */
    public function destroyTier(PrintSizeTier $tier)
    {
        $name = $tier->name;

        DB::transaction(function () use ($tier) {
            $this->removeFromPricingDraft('tier_ids', $tier->id);
            $tier->delete();
        });

        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã xoá bậc khổ ' . $name . '.']);
    }

    public function toggleTier(Request $request, PrintSizeTier $tier)
    {
        $tier->update(['is_active' => $request->boolean('is_active')]);
        $this->notifier->markDirty();

        return response()->json([
            'success' => $tier->is_active
                ? 'Đã bật bậc khổ ' . $tier->name . '.'
                : 'Đã tắt bậc khổ ' . $tier->name . ' — đơn cũ giữ nguyên.',
        ]);
    }

    private function validatedTier(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:40',
            'width_mm' => 'required|integer|min:1|max:2000',
            'height_mm' => 'required|integer|min:1|max:2000',
        ]);
    }

    /**
     * Bỏ một kỹ thuật/bậc khổ khỏi bản nháp mà không làm điều kiện giá bị mở
     * rộng ngoài ý muốn. Quy tắc nào có tham chiếu bị xoá sẽ được tắt để chủ
     * shop chủ động sửa lại trước khi xuất bản.
     */
    private function removeFromPricingDraft(string $conditionKey, int $removedId): void
    {
        $draft = PrintPricing::draft();

        if ($conditionKey === 'technique_ids') {
            $draft['cells'] = array_filter(
                (array) ($draft['cells'] ?? []),
                fn ($row, $id) => (int) $id !== $removedId,
                ARRAY_FILTER_USE_BOTH,
            );
            foreach ((array) ($draft['blank_technique_prices'] ?? []) as $blankId => $prices) {
                if (!is_array($prices)) {
                    continue;
                }

                unset($prices[(string) $removedId], $prices[$removedId]);
                $draft['blank_technique_prices'][$blankId] = $prices;
            }
        } else {
            foreach ((array) ($draft['cells'] ?? []) as $techniqueId => $row) {
                if (!is_array($row)) {
                    continue;
                }

                $draft['cells'][$techniqueId] = array_filter(
                    $row,
                    fn ($price, $tierId) => (int) $tierId !== $removedId,
                    ARRAY_FILTER_USE_BOTH,
                );
            }
        }

        foreach ((array) ($draft['rules'] ?? []) as $index => $rule) {
            $ids = $rule['when'][$conditionKey] ?? null;
            if (!is_array($ids)) {
                continue;
            }

            $remaining = array_values(array_filter(
                $ids,
                fn ($id) => (int) $id !== $removedId,
            ));

            if (count($remaining) === count($ids)) {
                continue;
            }

            // Không xoá điều kiện rồi để rule vô tình áp dụng cho tất cả.
            $draft['rules'][$index]['when'][$conditionKey] = $remaining;
            $draft['rules'][$index]['enabled'] = false;
        }

        PrintPricing::putDraft($draft);
    }

    /**
     * Dọn giới hạn kỹ thuật của sticker khi kỹ thuật bị xoá.
     *
     * Nếu sticker chỉ dành cho kỹ thuật đã xoá thì tắt sticker để không vô tình
     * biến nó thành sticker dùng được với mọi kỹ thuật.
     */
    private function removeTechniqueFromAssets(int $removedId): void
    {
        PrintAsset::query()
            ->whereNotNull('allowed_technique_ids')
            ->get()
            ->each(function (PrintAsset $asset) use ($removedId) {
                $allowed = (array) $asset->allowed_technique_ids;
                if (!in_array($removedId, array_map('intval', $allowed), true)) {
                    return;
                }

                $remaining = array_values(array_filter(
                    $allowed,
                    fn ($id) => (int) $id !== $removedId,
                ));

                $asset->update([
                    'allowed_technique_ids' => $remaining ?: null,
                    'is_active' => $remaining ? $asset->is_active : false,
                ]);
            });
    }
}
