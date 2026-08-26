<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\PrintBlank;
use App\Models\PrintBlankColor;
use App\Models\PrintDesign;
use App\Models\PrintMockup;
use App\Models\PrintTechnique;
use App\Models\Product;
use App\Services\PrintPositions;
use App\Services\StorefrontNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Quản lý phôi in: màu áo, vị trí in bán được, ảnh mockup.
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
            'blanks' => PrintBlank::with(['colors', 'mockups', 'techniques', 'product', 'category'])
                ->orderBy('sort_order')->orderBy('id')->get(),
            'techniques' => PrintTechnique::where('is_active', true)->orderBy('sort_order')->get(),
            // Chỉ sản phẩm còn sống mới nối được; danh sách gọn để chọn nhanh.
            'products' => Product::orderBy('product_name')->get(['id', 'product_name', 'sell_price']),
            // Cùng bảng danh mục với hàng bán sẵn — xem migration
            // add_category_to_print_blanks. Kèm parent_id để dựng nhóm cha-con
            // trong ô chọn; danh mục tắt không cho chọn mới.
            'categories' => Categories::where('status', 1)
                ->orderBy('sort_order')->orderBy('name')
                ->get(['id', 'parent_id', 'name']),
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
            'success' => 'Đã tạo phôi "' . $blank->name . '". Bước tiếp theo: tải ảnh mockup lên.',
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

    /**
     * Bật/tắt — cách ẩn phôi khỏi web mà không đụng gì tới đơn cũ.
     *
     * Đây vẫn là đường nên đi trong hầu hết trường hợp; xoá hẳn chỉ dành cho phôi
     * khai nhầm, xem destroy() bên dưới.
     */
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

    /**
     * Xoá hẳn một phôi — dành cho phôi khai nhầm, không phải phôi ngừng bán.
     *
     * CHẶN nếu còn thiết kế nào trỏ vào: `print_designs.print_blank_id` đặt
     * restrictOnDelete, nên không chặn ở đây thì cơ sở dữ liệu ném ra một lỗi ràng
     * buộc khoá ngoại mà nhân viên bán hàng không đọc nổi. Phôi ngừng bán thì tắt,
     * đừng xoá: hoá đơn tháng trước còn phải đọc được tên phôi.
     */
    public function destroy(PrintBlank $blank)
    {
        $designs = PrintDesign::where('print_blank_id', $blank->id)->count();

        if ($designs > 0) {
            return response()->json([
                'error' => 'Không xoá được phôi "' . $blank->name . '": đang có ' . $designs
                    . ' thiết kế của khách trỏ vào nó. Tắt phôi để ẩn khỏi web — đơn cũ giữ nguyên.',
            ], 422);
        }

        $name = $blank->name;
        // Gom đường dẫn TRƯỚC khi xoá; sau khi xoá thì không còn bản ghi để hỏi.
        $paths = $blank->mockups()->pluck('path')->all();

        DB::transaction(function () use ($blank) {
            // Khoá ngoại của màu, mockup và pivot kỹ thuật đều đặt cascade, nhưng
            // dọn tay để thứ tự xoá là thứ đọc được ở đây chứ không nằm ẩn trong
            // lược đồ — và để bản cài nào tắt kiểm khoá ngoại vẫn sạch.
            $blank->techniques()->detach();
            PrintMockup::where('print_blank_id', $blank->id)->delete();
            // Hỏi thẳng bảng màu thay vì qua quan hệ: quan hệ colors() có sẵn
            // orderBy, mà DELETE kèm ORDER BY thì mỗi hệ quản trị hiểu một kiểu.
            PrintBlankColor::where('print_blank_id', $blank->id)->delete();
            $blank->delete();
        });

        // Dọn tệp SAU khi cơ sở dữ liệu đã chốt: giao dịch quay lui mà ảnh đã xoá
        // là bản ghi còn nguyên nhưng trỏ vào chỗ trống.
        Storage::delete($paths);
        $this->notifier->markDirty();

        return response()->json([
            'success' => 'Đã xoá phôi "' . $name . '"'
                . ($paths ? ' cùng ' . count($paths) . ' tấm mockup.' : '.'),
        ]);
    }

    /**
     * Xoá hẳn một màu áo khỏi phôi.
     *
     * An toàn với đơn cũ vì `print_designs` lưu `color_name` dưới dạng chuỗi chứ
     * không trỏ khoá ngoại sang đây — hoá đơn cũ vẫn đọc được "Đen", "Trắng".
     * Nhưng ảnh mockup thì gắn khoá ngoại cascade, nên xoá màu là mất theo mọi tấm
     * chụp riêng cho màu đó; đếm ra để báo trước cho người bấm.
     */
    public function destroyColor(PrintBlankColor $color)
    {
        $name = $color->name;
        $paths = $color->mockups()->pluck('path')->all();

        DB::transaction(function () use ($color) {
            $color->mockups()->delete();
            $color->delete();
        });

        Storage::delete($paths);
        $this->notifier->markDirty();

        return response()->json([
            'success' => 'Đã xoá màu "' . $name . '"'
                . ($paths ? ' cùng ' . count($paths) . ' tấm mockup của màu này.' : '.'),
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'product_id' => 'nullable|exists:products,id',
            // Để trống được: phôi chưa xếp danh mục vẫn bán bình thường, chỉ là
            // không rơi vào chip lọc nào bên web.
            'categories_id' => 'nullable|exists:categories,id',
            'base_price' => 'required|integer|min:0',
            'frame_width_mm' => 'required|integer|min:50|max:2000',
            'frame_height_mm' => 'required|integer|min:50|max:2000',
            // Bốn vị trí in là hằng số trong mã nguồn; ở đây chỉ tick bật/tắt.
            // Bỏ tick sạch thì chặn hẳn: tự bật lại giúp là nói dối người dùng,
            // còn lưu một phôi không in được chỗ nào là bày ra thứ không bán được.
            'positions' => 'required|array|min:1',
            'positions.*' => 'string|in:' . implode(',', PrintPositions::keys()),
            'moq' => 'required|integer|min:1|max:9999',
            'lead_days' => 'required|integer|min:0|max:365',
            'technique_ids' => 'nullable|array',
            'technique_ids.*' => 'integer|exists:print_techniques,id',
            'colors' => 'nullable|array|max:40',
            'colors.*.name' => 'required|string|max:80',
            'colors.*.hex' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'colors.*.tone' => 'nullable|in:light,dark',
        ], [
            'positions.required' => 'Phôi phải bán được ít nhất một vị trí in.',
            'positions.min' => 'Phôi phải bán được ít nhất một vị trí in.',
        ]);
    }

    private function blankAttributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'product_id' => $data['product_id'] ?? null,
            'categories_id' => $data['categories_id'] ?? null,
            'base_price' => (int) $data['base_price'],
            'frame_width_mm' => (int) $data['frame_width_mm'],
            'frame_height_mm' => (int) $data['frame_height_mm'],
            'positions' => PrintPositions::normalise($data['positions'] ?? null),
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

    // ── Ảnh mockup ───────────────────────────────────────────────────

    /**
     * Tải một tấm mockup lên và KIỂM KHUNG ngay tại đây.
     *
     * Hiệu chuẩn khung ảnh khai một lần cho cả phôi nhưng mockup tải lên theo
     * từng màu. Tấm nào cắt cúp khác là mỗi milimét quy đổi ra một chỗ khác trên
     * tấm đó — mà không ai phát hiện cho tới lúc in hỏng. So TỈ LỆ chứ không so
     * số pixel:
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

        /*
         * Tấm ảnh và hiệu chuẩn khung phải cùng tỉ lệ.
         *
         * Ảnh chụp thật thì một milimét nằm ngang và một milimét nằm dọc dài
         * bằng nhau. Khai 520×700 mm cho một tấm 1000×1150 px là bảo hệ thống
         * rằng chiều ngang co khác chiều dọc: logo vuông khách kéo hiện ra thành
         * chữ nhật, và con số mm gửi xuống xưởng lệch theo đúng chừng ấy.
         *
         * Cảnh báo chứ không chặn — đôi khi tấm ảnh cố tình cắt cúp và chủ shop
         * biết mình đang làm gì. Nhưng phải nói ra, vì đây là kiểu sai không ai
         * nhìn thấy cho tới lúc cầm chiếc áo in xong trên tay.
         */
        $calibrationDrift = PrintMockup::aspectDrift(
            $width,
            $height,
            $blank->frame_width_mm,
            $blank->frame_height_mm,
        );

        $note = $calibrationDrift > PrintMockup::MAX_ASPECT_DRIFT
            ? sprintf(
                ' Lưu ý: tấm này lệch %.1f%% so với hiệu chuẩn khung ảnh (%d×%d mm). '
                . 'Hình khách kéo sẽ hiện méo và mm gửi xưởng lệch theo — soi lại hai số hiệu chuẩn.',
                $calibrationDrift,
                $blank->frame_width_mm,
                $blank->frame_height_mm,
            )
            : '';

        return response()->json([
            'success' => 'Đã tải mockup lên.' . $note,
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
