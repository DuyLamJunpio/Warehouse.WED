<?php

namespace App\Http\Controllers;

use App\Models\PrintTechnique;
use App\Models\PrintAsset;
use App\Models\PrintDesign;
use App\Services\PrintPricing;
use App\Services\StorefrontNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/** Quản lý tên và giá cố định của kỹ thuật; tự lưu phiên bản giá khi thay đổi. */
class PrintTechniqueController extends Controller
{
    public function __construct(private StorefrontNotifier $notifier)
    {
    }

    public function index()
    {
        $techniques = PrintTechnique::orderBy('sort_order')->orderBy('id')->get();
        return view('print.techniques', compact('techniques'));
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

        $technique = DB::transaction(function () use ($data, $request) {
            $technique = PrintTechnique::create($data);
            PrintPricing::publish('Lưu kỹ thuật in', $request->user()?->id);
            return $technique;
        });
        $this->notifier->markDirty();

        return response()->json([
            'success' => 'Đã tạo "' . $technique->name . '". Giá được áp dụng ngay.',
            'id' => $technique->id,
        ]);
    }

    public function update(Request $request, PrintTechnique $technique)
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($technique, $data, $request) {
            $technique->update($data);
            PrintPricing::publish('Lưu kỹ thuật in', $request->user()?->id);
        });
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
            PrintPricing::publish('Xóa kỹ thuật in');
        });

        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã xoá kỹ thuật "' . $name . '".']);
    }

    /** Bật/tắt. Đây là thứ thay cho nút xoá. */
    public function toggle(Request $request, PrintTechnique $technique)
    {
        DB::transaction(function () use ($technique, $request) {
            $technique->update(['is_active' => $request->boolean('is_active')]);
            PrintPricing::publish('Bật/tắt kỹ thuật in', $request->user()?->id);
        });
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
            'price' => 'required|integer|min:0|max:1000000000',
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
