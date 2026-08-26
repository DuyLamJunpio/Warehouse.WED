<?php

namespace App\Http\Controllers;

use App\Models\PrintAsset;
use App\Models\PrintFont;
use App\Models\PrintTechnique;
use App\Services\StorefrontNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Thư viện sticker và logo shop cung cấp sẵn cho khách.
 *
 * Mỗi mục mang theo ràng buộc riêng chứ không chỉ là một tấm ảnh: giá, kỹ thuật
 * nào dùng được, và giới hạn phóng to để khách không kéo ra vỡ nét.
 *
 * Chỉ liệt kê `kind = library`. File khách tự tải lên cũng nằm trong bảng này
 * nhưng thuộc về đơn của họ, không phải hàng của shop.
 */
class PrintAssetController extends Controller
{
    public function __construct(private StorefrontNotifier $notifier)
    {
    }

    public function index()
    {
        return view('print.library', [
            'assets' => PrintAsset::where('kind', PrintAsset::KIND_LIBRARY)
                ->orderBy('sort_order')->orderBy('id')->get(),
            'techniques' => PrintTechnique::where('is_active', true)->orderBy('sort_order')->get(),
            'fonts' => PrintFont::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|image|mimes:png,svg,webp|max:8192',
            'name' => 'required|string|max:150',
            'tag' => 'nullable|string|max:60',
            'fee' => 'required|integer|min:0',
            'min_width_mm' => 'required|integer|min:1|max:2000',
            'max_width_mm' => 'required|integer|min:1|max:2000',
            'technique_ids' => 'nullable|array',
            'technique_ids.*' => 'integer|exists:print_techniques,id',
        ]);

        $file = $request->file('file');
        $isSvg = $file->getMimeType() === 'image/svg+xml';

        /*
         * Ảnh bitmap phải đọc được số pixel: DPI lúc in tính từ đó. Vector thì
         * phóng bao nhiêu cũng nét nên gán một con số đủ lớn để studio không bao
         * giờ cảnh báo DPI cho nó.
         */
        $width = $height = 4000;
        if (!$isSvg) {
            $size = @getimagesize($file->getRealPath());
            if (!$size) {
                return response()->json(['error' => 'Không đọc được kích thước ảnh.'], 422);
            }
            [$width, $height] = $size;
        }

        // PNG không có kênh trong suốt sẽ in ra một khối chữ nhật trắng trên áo.
        $hasAlpha = $isSvg || $this->hasAlphaChannel($file->getRealPath());

        $asset = PrintAsset::create([
            'kind' => PrintAsset::KIND_LIBRARY,
            'name' => $data['name'],
            'tag' => $data['tag'] ?? null,
            'path' => $file->store('public/images'),
            'width_px' => $width,
            'height_px' => $height,
            'mime' => $file->getMimeType(),
            'bytes' => $file->getSize(),
            'has_alpha' => $hasAlpha,
            'fee' => (int) $data['fee'],
            // Mảng rỗng nghĩa là "mọi kỹ thuật", lưu null cho đúng ý đó.
            'allowed_technique_ids' => $data['technique_ids'] ?: null,
            'min_width_mm' => (int) $data['min_width_mm'],
            'max_width_mm' => (int) $data['max_width_mm'],
            'sort_order' => (int) PrintAsset::max('sort_order') + 1,
            'is_active' => true,
        ]);

        $this->notifier->markDirty();

        return response()->json([
            'success' => $hasAlpha
                ? 'Đã thêm "' . $asset->name . '" vào thư viện.'
                : 'Đã thêm "' . $asset->name . '", nhưng ảnh KHÔNG có nền trong suốt — in ra sẽ thành khối chữ nhật.',
            'has_alpha' => $hasAlpha,
        ]);
    }

    public function update(Request $request, PrintAsset $asset)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'tag' => 'nullable|string|max:60',
            'fee' => 'required|integer|min:0',
            'min_width_mm' => 'required|integer|min:1|max:2000',
            'max_width_mm' => 'required|integer|min:1|max:2000',
            'technique_ids' => 'nullable|array',
            'technique_ids.*' => 'integer|exists:print_techniques,id',
        ]);

        $asset->update([
            'name' => $data['name'],
            'tag' => $data['tag'] ?? null,
            'fee' => (int) $data['fee'],
            'allowed_technique_ids' => $data['technique_ids'] ?: null,
            'min_width_mm' => (int) $data['min_width_mm'],
            'max_width_mm' => (int) $data['max_width_mm'],
        ]);

        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã lưu "' . $asset->name . '".']);
    }

    /** Bật/tắt. Không xoá: thiết kế cũ của khách đang trỏ vào sticker này. */
    public function toggle(Request $request, PrintAsset $asset)
    {
        $asset->update(['is_active' => $request->boolean('is_active')]);
        $this->notifier->markDirty();

        return response()->json([
            'success' => $asset->is_active
                ? 'Đã bật "' . $asset->name . '".'
                : 'Đã tắt "' . $asset->name . '" — ẩn khỏi studio, thiết kế cũ giữ nguyên.',
        ]);
    }

    /**
     * PNG có kênh alpha không.
     *
     * Đọc byte thứ 25 của phần header IHDR: đó là color type, 4 và 6 là hai
     * dạng có alpha. Rẻ hơn nhiều so với nạp cả ảnh vào bộ nhớ chỉ để hỏi một bit.
     */
    private function hasAlphaChannel(string $path): bool
    {
        $handle = @fopen($path, 'rb');
        if (!$handle) {
            return false;
        }

        $header = fread($handle, 26);
        fclose($handle);

        if (!$header || substr($header, 1, 3) !== 'PNG') {
            return false;
        }

        return in_array(ord($header[25]), [4, 6], true);
    }

    // ── Phông chữ ────────────────────────────────────────────────────

    /**
     * Thêm một phông chữ.
     *
     * Tệp là TUỲ CHỌN: phông hệ thống (Arial, Georgia) thì máy nào cũng có sẵn,
     * chỉ cần khai ngăn xếp CSS. Có tải tệp lên thì hệ thống TỰ đặt tên CSS cho
     * nó — chủ shop không phải biết `font-family` là gì, và cũng không đặt trùng
     * tên với một phông khác được.
     *
     * Nhớ rằng danh sách này là lời hứa với khách: mỗi phông ở đây phải có một
     * tệp thật nằm trong máy của xưởng, nếu không đơn nhận về sẽ không dàn nổi.
     */
    public function storeFont(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'file' => 'nullable|file|mimes:woff2,woff,ttf,otf|max:4096',
            'family' => 'nullable|string|max:255',
        ]);

        $font = PrintFont::create([
            'name' => $data['name'],
            // Tạm thời; sửa lại ngay sau khi có id để tên CSS là duy nhất.
            'family' => $data['family'] ?: 'sans-serif',
            'sort_order' => (int) PrintFont::max('sort_order') + 1,
            'is_active' => true,
        ]);

        if ($request->hasFile('file')) {
            $font->update([
                'file_path' => $request->file('file')->store('public/fonts'),
                'family' => sprintf('"print-font-%d", sans-serif', $font->id),
            ]);
        }

        $this->notifier->markDirty();

        return response()->json([
            'success' => $font->file_path
                ? 'Đã thêm phông "' . $font->name . '". Studio sẽ hiện đúng mặt chữ này.'
                : 'Đã thêm phông "' . $font->name . '" dùng phông hệ thống. Nhớ chắc là xưởng cũng có nó.',
        ]);
    }

    public function updateFont(Request $request, PrintFont $font)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'family' => 'required|string|max:255',
        ]);

        // Có tệp riêng thì tên CSS do hệ thống giữ, không cho sửa tay — sửa là
        // studio mất mặt chữ mà không ai hiểu vì sao.
        $font->update([
            'name' => $data['name'],
            'family' => $font->file_path ? $font->family : $data['family'],
        ]);

        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã lưu phông "' . $font->name . '".']);
    }

    /** Bật/tắt. Không xoá: thiết kế cũ của khách đang dùng phông này. */
    public function toggleFont(Request $request, PrintFont $font)
    {
        $font->update(['is_active' => $request->boolean('is_active')]);
        $this->notifier->markDirty();

        return response()->json([
            'success' => $font->is_active
                ? 'Đã bật phông "' . $font->name . '".'
                : 'Đã tắt phông "' . $font->name . '" — ẩn khỏi studio, thiết kế cũ giữ nguyên.',
        ]);
    }
}
