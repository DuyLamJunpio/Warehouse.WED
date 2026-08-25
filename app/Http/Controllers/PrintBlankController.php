<?php

namespace App\Http\Controllers;

use App\Models\PrintBlank;
use App\Models\PrintBlankColor;
use App\Models\PrintMockup;
use App\Models\PrintTechnique;
use App\Models\PrintZone;
use App\Models\Product;
use App\Services\StorefrontNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Quản lý phôi in: màu áo, vùng in, ảnh mockup.
 *
 * Nối vào sản phẩm trong kho là TUỲ CHỌN. Không nối thì phôi đứng riêng với giá
 * khai tay — đó là đường mặc định, vì hầu hết shop in áo đặt phôi từ nhà cung
 * cấp và không đếm tồn theo từng màu x size.
 */
class PrintBlankController extends Controller
{
    public function __construct(private StorefrontNotifier $notifier)
    {
    }

    public function index()
    {
        return view('print.blanks', [
            'blanks' => PrintBlank::with(['colors', 'zones', 'mockups', 'techniques', 'product'])
                ->orderBy('sort_order')->orderBy('id')->get(),
            'techniques' => PrintTechnique::where('is_active', true)->orderBy('sort_order')->get(),
            // Chỉ sản phẩm còn sống mới nối được; danh sách gọn để chọn nhanh.
            'products' => Product::orderBy('product_name')->get(['id', 'product_name', 'sell_price']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $blank = DB::transaction(function () use ($data, $request) {
            $blank = PrintBlank::create($this->blankAttributes($data) + [
                'slug' => $this->uniqueSlug($data['name']),
                'sort_order' => (int) PrintBlank::max('sort_order') + 1,
                'is_active' => true,
            ]);

            $this->syncColors($blank, $data['colors'] ?? []);
            $blank->techniques()->sync($data['technique_ids'] ?? []);

            return $blank;
        });

        $this->notifier->markDirty();

        return response()->json([
            'success' => 'Đã tạo phôi "' . $blank->name . '". Bước tiếp theo: tải mockup lên rồi khai vùng in.',
            'id' => $blank->id,
        ]);
    }

    public function update(Request $request, PrintBlank $blank)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($blank, $data) {
            $blank->update($this->blankAttributes($data));
            $this->syncColors($blank, $data['colors'] ?? []);
            $blank->techniques()->sync($data['technique_ids'] ?? []);
        });

        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã lưu phôi "' . $blank->name . '".']);
    }

    /** Bật/tắt. Không có xoá: đơn cũ đang trỏ vào phôi này. */
    public function toggle(Request $request, PrintBlank $blank)
    {
        $blank->update(['is_active' => $request->boolean('is_active')]);
        $this->notifier->markDirty();

        return response()->json([
            'success' => $blank->is_active
                ? 'Đã bật phôi "' . $blank->name . '".'
                : 'Đã tắt phôi "' . $blank->name . '" — ẩn khỏi web, đơn cũ giữ nguyên.',
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'product_id' => 'nullable|exists:products,id',
            'base_price' => 'required|integer|min:0',
            'frame_width_mm' => 'required|integer|min:50|max:2000',
            'frame_height_mm' => 'required|integer|min:50|max:2000',
            'moq' => 'required|integer|min:1|max:9999',
            'lead_days' => 'required|integer|min:0|max:365',
            'technique_ids' => 'nullable|array',
            'technique_ids.*' => 'integer|exists:print_techniques,id',
            'colors' => 'nullable|array|max:40',
            'colors.*.name' => 'required|string|max:80',
            'colors.*.hex' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'colors.*.tone' => 'nullable|in:light,dark',
        ]);
    }

    private function blankAttributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'product_id' => $data['product_id'] ?? null,
            'base_price' => (int) $data['base_price'],
            'frame_width_mm' => (int) $data['frame_width_mm'],
            'frame_height_mm' => (int) $data['frame_height_mm'],
            'moq' => (int) $data['moq'],
            'lead_days' => (int) $data['lead_days'],
        ];
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name) ?: 'phoi-' . Str::lower(Str::random(6));

        return PrintBlank::where('slug', $slug)->exists()
            ? $slug . '-' . Str::lower(Str::random(4))
            : $slug;
    }

    /**
     * Hoà danh sách màu theo TÊN, không xoá rồi tạo lại.
     *
     * Ảnh mockup gắn khoá ngoại vào từng màu; xoá sạch rồi chèn lại là mọi tấm
     * mockup đã tải lên bay theo. Màu bị bỏ khỏi form thì tắt chứ không xoá.
     */
    private function syncColors(PrintBlank $blank, array $colors): void
    {
        $existing = $blank->colors()->get()->keyBy('name');
        $seen = [];

        foreach (array_values($colors) as $i => $color) {
            $name = trim($color['name']);
            $seen[] = $name;

            $attributes = [
                'hex' => strtolower($color['hex']),
                // Tông để trống thì suy từ độ sáng; đây chỉ là gợi ý, người dùng
                // sửa đè được vì xám mélange nằm đúng giữa.
                'tone' => $color['tone'] ?? PrintBlankColor::suggestTone($color['hex']),
                'sort_order' => $i,
                'is_active' => true,
            ];

            if ($existing->has($name)) {
                $existing[$name]->update($attributes);
                continue;
            }

            $blank->colors()->create($attributes + ['name' => $name]);
        }

        $blank->colors()->whereNotIn('name', $seen ?: ['__none__'])->update(['is_active' => false]);
    }

    // ── Vùng in ──────────────────────────────────────────────────────

    public function storeZone(Request $request, PrintBlank $blank)
    {
        $data = $this->validatedZone($request);

        $zone = $blank->zones()->create($data + [
            'key' => 'z' . Str::lower(Str::random(6)),
            'sort_order' => (int) $blank->zones()->max('sort_order') + 1,
            'is_active' => true,
        ]);

        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã lưu vùng "' . $zone->label . '".', 'id' => $zone->id]);
    }

    public function updateZone(Request $request, PrintZone $zone)
    {
        $zone->update($this->validatedZone($request));
        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã lưu vùng "' . $zone->label . '".']);
    }

    public function toggleZone(Request $request, PrintZone $zone)
    {
        $zone->update(['is_active' => $request->boolean('is_active')]);
        $this->notifier->markDirty();

        return response()->json([
            'success' => $zone->is_active ? 'Đã bật vùng in.' : 'Đã tắt vùng in — đơn cũ giữ nguyên.',
        ]);
    }

    private function validatedZone(Request $request): array
    {
        return $request->validate([
            'label' => 'required|string|max:80',
            // mm là sự thật; box_* chỉ để vẽ khung lên mockup.
            'width_mm' => 'required|integer|min:5|max:2000',
            'height_mm' => 'required|integer|min:5|max:2000',
            'box_x' => 'required|numeric|min:0|max:100',
            'box_y' => 'required|numeric|min:0|max:100',
            'box_w' => 'required|numeric|min:0.5|max:100',
            'box_h' => 'required|numeric|min:0.5|max:100',
            'max_placements' => 'nullable|integer|min:1|max:50',
        ]);
    }

    // ── Ảnh mockup ───────────────────────────────────────────────────

    /**
     * Tải một tấm mockup lên và KIỂM KHUNG ngay tại đây.
     *
     * Vùng in khai một lần cho cả phôi nhưng mockup up theo từng màu. Tấm nào
     * cắt cúp khác là khung in đúng trên một tấm và sai trên phần còn lại — mà
     * không ai phát hiện cho tới lúc in hỏng. So TỈ LỆ chứ không so số pixel:
     * 2000x2300 và 1000x1150 là cùng khung, chỉ khác độ phân giải.
     */
    public function uploadMockup(Request $request, PrintBlank $blank)
    {
        $data = $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,webp|max:8192',
            'print_blank_color_id' => 'nullable|exists:print_blank_colors,id',
            'view' => 'nullable|string|max:40',
            'force' => 'nullable|boolean',
        ]);

        $file = $request->file('file');
        $size = @getimagesize($file->getRealPath());

        if (!$size) {
            return response()->json(['error' => 'Không đọc được kích thước ảnh. Thử lưu lại ảnh rồi tải lên.'], 422);
        }

        [$width, $height] = $size;
        $reference = $blank->mockups()->whereNotNull('width_px')->where('width_px', '>', 0)->first();

        if ($reference && !$request->boolean('force')) {
            $drift = PrintMockup::aspectDrift($width, $height, $reference->width_px, $reference->height_px);

            if ($drift > PrintMockup::MAX_ASPECT_DRIFT) {
                return response()->json([
                    'error' => sprintf(
                        'Tấm này lệch khung %.1f%% so với tấm chuẩn (%dx%d). Khung in sẽ sai trên tấm này. '
                        . 'Chụp lại cùng khoảng cách và cùng khung cắt, hoặc bấm "Vẫn dùng" để tự chỉnh lệch sau.',
                        $drift, $reference->width_px, $reference->height_px,
                    ),
                    'drift' => round($drift, 1),
                    'needs_confirm' => true,
                ], 422);
            }
        }

        $mockup = $blank->mockups()->create([
            'print_blank_color_id' => $data['print_blank_color_id'] ?? null,
            'view' => $data['view'] ?? 'front',
            'path' => $file->store('public/images'),
            'width_px' => $width,
            'height_px' => $height,
        ]);

        $this->notifier->markDirty();

        return response()->json([
            'success' => 'Đã tải mockup lên. Khung vùng in đang hiện đè lên tấm này để bạn soi lệch.',
            'id' => $mockup->id,
            'url' => Storage::url($mockup->path),
        ]);
    }

    public function destroyMockup(PrintMockup $mockup)
    {
        Storage::delete($mockup->path);
        $mockup->delete();
        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã xoá tấm mockup.']);
    }
}
