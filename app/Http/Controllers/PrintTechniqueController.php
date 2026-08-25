<?php

namespace App\Http\Controllers;

use App\Models\PrintBlank;
use App\Models\PrintSizeTier;
use App\Models\PrintTechnique;
use App\Services\PrintPricing;
use App\Services\StorefrontNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Kỹ thuật in và bậc khổ — hai thứ chủ shop tự tạo được.
 *
 * Ràng buộc của kỹ thuật là DỮ LIỆU: số màu tối đa, có nhận ảnh chụp không, DPI
 * tối thiểu. Studio bên web đọc đúng mấy trường đó để chặn khách, nên tạo thêm
 * một kỹ thuật lạ không cần ai sửa code.
 *
 * Không có hàm xoá. "Xoá" ở giao diện thực chất là tắt: đơn cũ và quy tắc giá cũ
 * đang trỏ vào những bản ghi này.
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

        // Đếm tham chiếu treo: tắt một kỹ thuật thì nói ngay bao nhiêu phôi và
        // quy tắc đang dùng nó. Không chặn, chỉ nói.
        $usage = [];
        foreach ($techniques as $technique) {
            $usage[$technique->id] = [
                'blanks' => PrintBlank::whereHas('techniques', fn ($q) => $q->where('print_techniques.id', $technique->id))->count(),
                'rules' => collect($draft['rules'])
                    ->filter(fn ($r) => in_array($technique->id, (array) ($r['when']['technique_ids'] ?? [])))
                    ->count(),
                'priced' => collect($draft['cells'][$technique->id] ?? [])->filter(fn ($v) => $v !== null)->count(),
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

    /** Bật/tắt. Đây là thứ thay cho nút xoá. */
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
}
