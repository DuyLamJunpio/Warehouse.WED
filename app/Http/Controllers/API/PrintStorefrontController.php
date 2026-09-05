<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PrintAsset;
use App\Models\PrintBlank;
use App\Models\PrintDesign;
use App\Models\PrintFont;
use App\Models\PrintTechnique;
use App\Services\PrintPositions;
use App\Services\PrintPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Những gì studio đặt in bên web bán hàng đọc và ghi.
 *
 * Phân đôi rõ ràng:
 *
 *   - `catalogue` và `quote` CÔNG KHAI: chỉ đọc, không mang dữ liệu khách nào.
 *   - `assets` và `designs` nằm sau bí mật dùng chung: chúng ghi file và lưu
 *     thiết kế, gọi qua route handler của Next chứ không phải từ trình duyệt.
 *
 * Giá hiện trong studio do bản TypeScript tính cho mượt tay, nhưng con số tính
 * tiền thật luôn dựng lại ở `quote` và một lần nữa lúc chốt đơn.
 */
class PrintStorefrontController extends Controller
{
    /** Mọi thứ studio cần để dựng màn hình, trong một lần gọi. */
    public function catalogue()
    {
        $pricing = PrintPricing::current();

        $blanks = PrintBlank::with(['colors', 'mockups', 'techniques', 'product.variants', 'category'])
            ->where('is_active', true)
            ->orderBy('sort_order')->orderBy('id')
            ->get()
            ->map(fn (PrintBlank $blank) => $this->blankPayload($blank));

        return response()->json([
            'pricing_version_id' => PrintPricing::currentVersionId(),
            /*
             * Bốn vị trí in, kèm nhãn và trần milimét của từng chỗ.
             *
             * Web bán hàng KHÔNG chép lại bảng này — nó nhận từ đây. Chép sang
             * bên đó là sớm muộn hai bên lệch nhau, và lệch trần mm nghĩa là
             * studio cho khách kéo một khổ mà máy chủ từ chối ngay sau đó.
             */
            'pricing_mode' => $pricing['mode'] ?? 'legacy',
            'positions' => PrintPositions::payload(),
            'blanks' => $blanks,
            'techniques' => collect($pricing['techniques'] ?? [])->where('is_active', true)->filter(fn ($t) => ($t['price'] ?? null) !== null)->values(),
            'tiers' => $pricing['tiers'] ?? [],
            'cells' => $pricing['cells'] ?? [],
            'rules' => $pricing['rules'] ?? [],
            'qty_tiers' => $pricing['qty_tiers'] ?? [],
            'rounding' => $pricing['rounding'] ?? 0,
            'min_charge' => $pricing['min_charge'] ?? 0,
            // Phông chữ shop in được. Khách chỉ chọn trong số này — cho gõ tên
            // phông tự do là nhận về đơn xưởng không dàn nổi.
            'fonts' => PrintFont::where('is_active', true)
                ->orderBy('sort_order')->orderBy('id')
                ->get()
                ->map(fn (PrintFont $f) => $f->toStorefrontArray()),
            'library' => PrintAsset::where('kind', PrintAsset::KIND_LIBRARY)
                ->where('is_active', true)
                ->orderBy('sort_order')->orderBy('id')
                ->get()
                ->map(fn (PrintAsset $a) => $a->toStorefrontArray()),
        ]);
    }

    private function blankPayload(PrintBlank $blank): array
    {
        $sizeMap = $blank->sizeMap();

        return [
            'id' => $blank->id,
            'slug' => $blank->slug,
            'name' => $blank->name,
            'description' => $blank->description,
            'base_price' => $blank->effectiveBasePrice(),
            'product_id' => $blank->product_id,
            /*
             * Danh mục để web bán hàng dựng hàng nút lọc trên trang In áo.
             *
             * Bên đó KHÔNG có danh sách danh mục riêng: nó gom từ chính các phôi
             * gửi sang, nên chip lọc nào hiện ra cũng chắc chắn có phôi đặt được
             * đằng sau. Phôi chưa xếp danh mục trả `null` và rơi vào nhóm "Khác".
             *
             * Danh mục xoá mềm coi như chưa xếp: quan hệ category() cố ý
             * `withTrashed` để trang quản trị còn đọc được tên cũ, nhưng bày một
             * chip trỏ vào danh mục đã xoá thì khách bấm vào một cái tên chết.
             */
            'category' => $blank->category && !$blank->category->trashed()
                ? [
                    'id' => $blank->category->id,
                    'name' => $blank->category->name,
                    'slug' => $blank->category->slug,
                ]
                : null,
            'frame_width_mm' => $blank->frame_width_mm,
            'frame_height_mm' => $blank->frame_height_mm,
            'moq' => $blank->moq,
            'lead_days' => $blank->lead_days,
            'template_url' => $blank->template_path ? Storage::url($blank->template_path) : null,
            'technique_ids' => $blank->techniques->pluck('id')->all(),
            // Nối kho thì size lấy từ biến thể thật; không nối thì phôi chỉ có
            // một cỡ duy nhất, và nói thẳng ra thay vì để danh sách rỗng.
            'sizes' => $sizeMap ? array_keys($sizeMap) : ['Một cỡ'],
            // Size vẫn gửi sang để khách chọn áo, nhưng không còn làm thay đổi giá.
            'size_surcharge' => array_fill_keys(array_keys($sizeMap), 0),
            'size_pricing' => 'flat',
            'colors' => $blank->colors->where('is_active', true)->values()->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'hex' => $c->hex,
                'tone' => $c->tone,
            ]),
            'position_keys' => $blank->positionKeys(),
            'mockups' => $blank->mockups->map(fn ($m) => [
                'color_id' => $m->print_blank_color_id,
                'view' => $m->view,
                'url' => Storage::url($m->path),
                'width_px' => $m->width_px,
                'height_px' => $m->height_px,
                'offset_x' => $m->offset_x,
                'offset_y' => $m->offset_y,
            ]),
        ];
    }

    /**
     * Báo giá lại từ máy chủ.
     *
     * Studio gọi mỗi khi khách đổi thiết kế. Trình duyệt chỉ được nói THIẾT KẾ
     * gồm những gì; giá do bên này quyết, đúng như cách `priceCart` bên web bán
     * hàng không tin số tiền nào từ localStorage.
     */
    public function quote(Request $request)
    {
        $data = $this->validatedDesign($request);
        $blank = PrintBlank::with(['colors', 'product.variants'])->findOrFail($data['blank_id']);

        return response()->json($this->quoteFor($blank, $data));
    }

    private function validatedDesign(Request $request): array
    {
        return $request->validate([
            'blank_id' => 'required|exists:print_blanks,id',
            'technique_id' => 'required|exists:print_techniques,id',
            'color_name' => 'required|string|max:80',
            'size' => 'required|string|max:40',
            'ink_colors' => 'nullable|integer|min:1|max:99',
            'qty' => 'required|integer|min:1|max:5000',
            'placements' => 'present|array|max:30',
            // Bốn vị trí là hằng số trong mã nguồn, không phải dữ liệu — khoá
            // lạ bị chặn ngay ở đây thay vì đi tiếp thành một đơn không in được.
            'placements.*.position' => 'required|string|in:' . implode(',', PrintPositions::keys()),
            /*
             * Ảnh hay chữ. Hai loại đi chung một đường vì mọi thứ phía sau đối xử
             * với chúng như nhau — khung bao, bậc khổ, tiền in. Chỉ khác cái gì
             * được vẽ vào trong khung.
             */
            'placements.*.kind' => 'required|in:image,text',
            'placements.*.asset_id' => 'required_if:placements.*.kind,image|nullable|exists:print_assets,id',
            'placements.*.text_content' => 'required_if:placements.*.kind,text|nullable|string|max:80',
            'placements.*.text_font_id' => 'required_if:placements.*.kind,text|nullable|exists:print_fonts,id',
            'placements.*.text_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'placements.*.x_mm' => 'required|numeric',
            'placements.*.y_mm' => 'required|numeric',
            'placements.*.w_mm' => 'required|numeric|min:1',
            'placements.*.h_mm' => 'required|numeric|min:1',
            'placements.*.rotation' => 'nullable|numeric|min:-180|max:180',
        ]);
    }

    /**
     * Dựng đầu vào cho bộ máy giá từ dữ liệu thô của studio.
     *
     * Phí sticker và trần milimét của vị trí in đều đọc lại TỪ MÁY CHỦ, không
     * nhận từ trình duyệt: sửa một con số trong request không được phép làm rẻ
     * đơn đi, cũng không được phép in một khổ mà xưởng không nhận.
     */
    private function quoteFor(PrintBlank $blank, array $data): array
    {
        if (!$blank->techniques()->whereKey($data['technique_id'])->exists()) {
            return [
                'lines' => [],
                'unit_price' => 0,
                'total' => 0,
                'errors' => ['Kỹ thuật này chưa được bật cho phôi đã chọn.'],
                'warnings' => [],
            ];
        }

        $color = $blank->colors->firstWhere('name', $data['color_name']);
        $assets = PrintAsset::whereIn('id', collect($data['placements'])->pluck('asset_id')->filter())
            ->get()->keyBy('id');

        $placements = collect($data['placements'])->map(function (array $p) use ($assets) {
            // Chữ do khách tự gõ nên không có phí bản quyền nào để cộng.
            $asset = isset($p['asset_id']) ? $assets->get($p['asset_id']) : null;

            return [
                'position' => $p['position'],
                'x_mm' => (float) $p['x_mm'],
                'y_mm' => (float) $p['y_mm'],
                'w_mm' => (float) $p['w_mm'],
                'h_mm' => (float) $p['h_mm'],
                'rotation' => (float) ($p['rotation'] ?? 0),
                'asset_fee' => $asset?->fee ?? 0,
                'asset_name' => $asset?->name,
            ];
        })->all();

        $sizeMap = $blank->sizeMap();

        return PrintPricing::quote([
            'blank' => [
                'id' => $blank->id,
                'name' => $blank->name,
                'base_price' => $blank->effectiveBasePrice(),
                'moq' => $blank->moq,
                'product_id' => $blank->product_id,
            ],
            'size' => $data['size'],
            'size_surcharge' => 0,
            'color_name' => $data['color_name'],
            'tone' => $color?->tone ?? 'light',
            'technique_id' => (int) $data['technique_id'],
            'ink_colors' => (int) ($data['ink_colors'] ?? 1),
            'qty' => (int) $data['qty'],
            'positions' => PrintPositions::pricingMap($blank->positionKeys()),
            'placements' => $placements,
        ]);
    }

    /**
     * Nhận file thiết kế của khách.
     *
     * Đi qua đây thay vì để web bán hàng tự ghi thẳng lên Supabase, để khoá
     * Supabase chỉ nằm ở một nơi. Đổi lại là thêm một chặng mạng — chấp nhận
     * được với file vài chục MB, và bù lại nhân viên quản lý được file ngay
     * trong trang quản trị.
     */
    public function storeAsset(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:png,jpg,jpeg,webp,pdf,svg|max:25600',
            'name' => 'nullable|string|max:150',
        ]);

        $file = $request->file('file');
        $isRaster = str_starts_with((string) $file->getMimeType(), 'image/')
            && $file->getMimeType() !== 'image/svg+xml';

        // Ảnh bitmap phải đọc được số pixel: DPI lúc in tính từ đó, mà DPI là
        // thứ quyết định hình in ra có rỗ hay không. Vector thì phóng bao nhiêu
        // cũng nét, nên gán một con số đủ lớn để mọi cảnh báo DPI đều im.
        $width = $height = 4000;
        if ($isRaster) {
            $size = @getimagesize($file->getRealPath());
            if (!$size) {
                return response()->json(['error' => 'Không đọc được kích thước ảnh. Vui lòng gửi file PNG hoặc JPG hợp lệ.'], 422);
            }
            [$width, $height] = $size;
        }

        $asset = PrintAsset::create([
            'kind' => PrintAsset::KIND_UPLOAD,
            'name' => $data['name'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'path' => $file->store('public/images'),
            'width_px' => $width,
            'height_px' => $height,
            'mime' => $file->getMimeType(),
            'bytes' => $file->getSize(),
            'has_alpha' => $file->getMimeType() === 'image/png',
            'fee' => 0,
            'min_width_mm' => 10,
            'max_width_mm' => 2000,
            'is_active' => true,
        ]);

        return response()->json($asset->toStorefrontArray(), 201);
    }

    /**
     * Chốt một thiết kế.
     *
     * Giá được ĐÓNG BĂNG tại đây cùng id phiên bản bảng giá. Chủ shop sửa giá
     * sau đó thì đơn này vẫn giữ nguyên con số khách đã nhìn thấy lúc trả tiền.
     *
     * Mẫu mới chỉ là `draft`: khách còn có thể bỏ giỏ hoặc không thanh toán, nên nó
     * không được phép xuất hiện trong hàng đợi nhân viên duyệt. Lúc thanh toán PayOS
     * đã xác nhận, luồng hoàn tất đơn mới gắn mẫu vào hoá đơn, chuyển nó sang
     * `pending`.
     */
    public function storeDesign(Request $request)
    {
        $data = $this->validatedDesign($request);
        $blank = PrintBlank::with(['colors', 'product.variants'])->findOrFail($data['blank_id']);
        $quote = $this->quoteFor($blank, $data);

        // Thiết kế có lỗi thì KHÔNG lưu: một bản ghi giá 0 đồng nằm chờ duyệt
        // là thứ nhân viên sẽ tin nhầm.
        if ($quote['errors']) {
            return response()->json(['error' => $quote['errors'][0], 'errors' => $quote['errors']], 422);
        }

        $color = $blank->colors->firstWhere('name', $data['color_name']);
        $assets = PrintAsset::whereIn('id', collect($data['placements'])->pluck('asset_id')->filter())
            ->get()->keyBy('id');
        $fonts = PrintFont::whereIn('id', collect($data['placements'])->pluck('text_font_id')->filter())
            ->get()->keyBy('id');

        $design = PrintDesign::create([
            'code' => PrintDesign::newCode(),
            'print_blank_id' => $blank->id,
            'print_technique_id' => (int) $data['technique_id'],
            'color_name' => $data['color_name'],
            'color_tone' => $color?->tone ?? 'light',
            'size' => $data['size'],
            'ink_colors' => (int) ($data['ink_colors'] ?? 1),
            'qty' => (int) $data['qty'],
            // Chép kèm tên, link và số pixel của từng hình: bảng toạ độ giao cho
            // thợ in phải đọc được kể cả khi sau này sticker bị tắt đi.
            'placements' => collect($data['placements'])->map(function (array $p) use ($assets, $fonts) {
                $asset = isset($p['asset_id']) ? $assets->get($p['asset_id']) : null;
                $font = isset($p['text_font_id']) ? $fonts->get($p['text_font_id']) : null;

                return [
                    'kind' => $p['kind'],
                    'position' => $p['position'],
                    'asset_id' => $p['asset_id'] ?? null,
                    'asset_name' => $asset?->name,
                    'asset_url' => $asset?->url,
                    'asset_width_px' => $asset?->width_px,
                    'asset_height_px' => $asset?->height_px,
                    // Chép kèm TÊN và ngăn xếp phông, không chỉ id: bảng giao cho
                    // xưởng phải đọc được kể cả khi sau này phông bị tắt đi.
                    'text_content' => $p['text_content'] ?? null,
                    'text_font_id' => $p['text_font_id'] ?? null,
                    'text_font_name' => $font?->name,
                    'text_font_family' => $font?->family,
                    'text_color' => $p['text_color'] ?? null,
                    'x_mm' => (float) $p['x_mm'],
                    'y_mm' => (float) $p['y_mm'],
                    'w_mm' => (float) $p['w_mm'],
                    'h_mm' => (float) $p['h_mm'],
                    'rotation' => (float) ($p['rotation'] ?? 0),
                ];
            })->all(),
            'pricing_version_id' => PrintPricing::currentVersionId(),
            'price_breakdown' => $quote['lines'],
            'unit_price' => $quote['unit_price'],
            'total_price' => $quote['total'],
            'review_status' => PrintDesign::STATUS_DRAFT,
        ]);

        return response()->json([
            'code' => $design->code,
            'unit_price' => $design->unit_price,
            'total_price' => $design->total_price,
            'lines' => $quote['lines'],
            'warnings' => $quote['warnings'],
        ], 201);
    }

    /**
     * Tra một mẫu thiết kế theo mã.
     *
     * Web bán hàng gọi vào đây lúc dựng giỏ hàng và lúc thanh toán, để lấy GIÁ
     * ĐÃ ĐÓNG BĂNG chứ không tin con số nào trong localStorage. Trả về cả trạng
     * thái đã đặt hay chưa: mở lại tab cũ rồi bấm đặt lần nữa là chuyện thường.
     */
    public function showDesign(string $code)
    {
        $design = PrintDesign::with(['blank', 'technique'])->where('code', $code)->first();

        if (!$design) {
            return response()->json(['error' => 'Không tìm thấy mẫu thiết kế này.'], 404);
        }

        $ordered = $design->invoice_id !== null;

        return response()->json([
            'code' => $design->code,
            'blank_name' => $design->blank?->name,
            'blank_slug' => $design->blank?->slug,
            'technique_name' => $design->technique?->name,
            'color_name' => $design->color_name,
            'size' => $design->size,
            'qty' => $design->qty,
            'unit_price' => $design->unit_price,
            'total_price' => $design->total_price,
            'lines' => $design->price_breakdown ?? [],
            'lead_days' => max((int) $design->blank?->lead_days, (int) $design->technique?->lead_days),
            'review_status' => $design->review_status,
            'already_ordered' => $ordered,
            // Ảnh đầu tiên trong thiết kế, đủ để giỏ hàng có thứ hiển thị.
            'thumb_url' => collect($design->placements)->pluck('asset_url')->filter()->first(),
        ]);
    }
}
