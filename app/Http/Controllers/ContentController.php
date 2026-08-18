<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\SiteText;
use App\Services\StorefrontNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Nội dung trang chủ web bán hàng: slide hero, chữ chạy, tiêu đề các khối.
 *
 * Mục đích là để chủ shop tự đổi theo chương trình khuyến mãi hay dịp lễ tết
 * mà không phải nhờ ai sửa mã nguồn rồi build lại.
 */
class ContentController extends Controller
{
    /**
     * Khuyến nghị kích thước. Vừa hiện trong giao diện cho người tải ảnh biết,
     * vừa là căn cứ cho các luật kiểm tra bên dưới.
     */
    public const ANH_RONG_TOI_THIEU = 2400;
    public const ANH_CAO_TOI_THIEU = 1350;
    public const ANH_MB_TOI_DA = 2;
    public const VIDEO_MB_TOI_DA = 6;

    public function __construct(private StorefrontNotifier $notifier)
    {
    }

    public function index()
    {
        return view('content.index', [
            'banners' => Banner::orderBy('sort_order')->get(),
            'marquees' => SiteText::marquee()->orderBy('sort_order')->get(),
            'announcements' => SiteText::announcement()->orderBy('sort_order')->get(),
            'headings' => $this->headingValues(),
            'headingLabels' => SiteText::HEADINGS,
            'limits' => [
                'anh_rong' => self::ANH_RONG_TOI_THIEU,
                'anh_cao' => self::ANH_CAO_TOI_THIEU,
                'anh_mb' => self::ANH_MB_TOI_DA,
                'video_mb' => self::VIDEO_MB_TOI_DA,
            ],
        ]);
    }

    /** Giá trị tiêu đề hiện tại; chưa ai sửa thì lấy chữ mặc định. */
    private function headingValues(): array
    {
        $saved = SiteText::heading()->pluck('value', 'key');

        $out = [];
        foreach (SiteText::HEADINGS as $key => [$macDinh, $moTa]) {
            $out[$key] = $saved[$key] ?? $macDinh;
        }

        return $out;
    }

    // ── Slide hero ───────────────────────────────────────────────────

    public function storeBanner(Request $request)
    {
        $data = $this->validateBanner($request);

        $data['media_path'] = $this->luuFile($request, 'media');
        $data['media_type'] = $this->laVideo($request) ? Banner::TYPE_VIDEO : Banner::TYPE_IMAGE;
        $data['poster_path'] = $this->luuFile($request, 'poster');
        $data['mobile_path'] = $this->luuFile($request, 'mobile');
        $data['sort_order'] = (int) Banner::max('sort_order') + 1;
        $data['status'] = $request->boolean('status', true);

        $banner = Banner::create($data);
        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã thêm slide.', 'id' => $banner->id]);
    }

    public function updateBanner(Request $request, string $id)
    {
        $banner = Banner::findOrFail($id);
        $data = $this->validateBanner($request, $banner);

        foreach ([['media', 'media_path'], ['poster', 'poster_path'], ['mobile', 'mobile_path']] as [$field, $col]) {
            if (!$request->hasFile($field)) {
                continue;
            }
            // Xoá file cũ để storage không tồn rác.
            if ($banner->$col) {
                Storage::delete($banner->$col);
            }
            $data[$col] = $this->luuFile($request, $field);
        }

        if ($request->hasFile('media')) {
            $data['media_type'] = $this->laVideo($request) ? Banner::TYPE_VIDEO : Banner::TYPE_IMAGE;
        }

        $data['status'] = $request->boolean('status', true);
        $banner->update($data);
        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã lưu slide.']);
    }

    public function destroyBanner(string $id)
    {
        $banner = Banner::findOrFail($id);

        foreach (['media_path', 'poster_path', 'mobile_path'] as $col) {
            if ($banner->$col) {
                Storage::delete($banner->$col);
            }
        }

        $banner->delete();
        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã xoá slide.']);
    }

    public function reorderBanner(Request $request, string $id)
    {
        $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);

        $banner = Banner::findOrFail($id);
        $isUp = $request->input('direction') === 'up';

        $neighbour = Banner::where('sort_order', $isUp ? '<' : '>', $banner->sort_order)
            ->orderBy('sort_order', $isUp ? 'desc' : 'asc')
            ->first();

        if (!$neighbour) {
            return response()->json(['error' => 'Slide đã ở đầu hoặc cuối danh sách.'], 422);
        }

        [$banner->sort_order, $neighbour->sort_order] = [$neighbour->sort_order, $banner->sort_order];
        $banner->save();
        $neighbour->save();
        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã đổi thứ tự.']);
    }

    private function laVideo(Request $request): bool
    {
        return str_starts_with((string) $request->file('media')->getMimeType(), 'video/');
    }

    /**
     * Kiểm tra file tải lên theo đúng khuyến nghị kích thước.
     *
     * Ảnh hẹp hơn 2400px sẽ vỡ trên màn lớn vì hero trải hết chiều rộng màn
     * hình. Video quá nặng làm trang tải chậm trên mạng di động.
     */
    private function validateBanner(Request $request, ?Banner $banner = null): array
    {
        $luatMedia = ['file', 'mimetypes:image/jpeg,image/png,image/webp,image/avif,video/mp4,video/webm'];

        if ($request->hasFile('media') && !$this->laVideo($request)) {
            $luatMedia[] = 'max:' . self::ANH_MB_TOI_DA * 1024;
            $luatMedia[] = 'dimensions:min_width=' . self::ANH_RONG_TOI_THIEU
                . ',min_height=' . self::ANH_CAO_TOI_THIEU;
        } else {
            $luatMedia[] = 'max:' . self::VIDEO_MB_TOI_DA * 1024;
        }

        return $request->validate([
            'media' => array_merge($banner ? ['nullable'] : ['required'], $luatMedia),
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'mobile' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:2048'],
            'alt' => ['nullable', 'string', 'max:255'],
            'heading' => ['nullable', 'string', 'max:255'],
            'subheading' => ['nullable', 'string', 'max:500'],
            'cta_label' => ['nullable', 'string', 'max:60'],
            'cta_link' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ], [
            'media.required' => 'Chưa chọn ảnh hoặc video cho slide.',
            'media.dimensions' => 'Ảnh phải rộng ít nhất ' . self::ANH_RONG_TOI_THIEU . 'px và cao ít nhất '
                . self::ANH_CAO_TOI_THIEU . 'px, nếu không sẽ bị vỡ trên màn hình lớn.',
            'media.max' => 'File quá nặng. Ảnh tối đa ' . self::ANH_MB_TOI_DA . 'MB, video tối đa '
                . self::VIDEO_MB_TOI_DA . 'MB.',
            'media.mimetypes' => 'Chỉ nhận ảnh JPG/PNG/WebP/AVIF hoặc video MP4/WebM.',
            'ends_at.after_or_equal' => 'Ngày kết thúc phải sau ngày bắt đầu.',
        ]);
    }

    private function luuFile(Request $request, string $field): ?string
    {
        return $request->hasFile($field)
            ? $request->file($field)->store('public/banners')
            : null;
    }

    // ── Chữ chạy ─────────────────────────────────────────────────────

    public function saveMarquee(Request $request)
    {
        // Cùng một hàm phục vụ hai dải chữ: dải nhỏ trên cùng (mọi trang) và
        // dải chữ lớn giữa trang chủ. Chúng khác nhau ở vị trí chứ không khác
        // cách lưu, nên tách hai hàm gần giống hệt nhau là thừa.
        $group = $request->input('group') === SiteText::GROUP_ANNOUNCEMENT
            ? SiteText::GROUP_ANNOUNCEMENT
            : SiteText::GROUP_MARQUEE;

        $data = $request->validate([
            'items' => ['present', 'array'],
            'items.*.value' => ['required', 'string', 'max:120'],
            'items.*.starts_at' => ['nullable', 'date'],
            'items.*.ends_at' => ['nullable', 'date'],
        ], [
            'items.*.value.required' => 'Dòng chữ chạy không được để trống.',
            'items.*.value.max' => 'Mỗi dòng chữ chạy tối đa 120 ký tự.',
        ]);

        // Lưu lại toàn bộ danh sách: dòng bị bỏ khỏi form nghĩa là người dùng đã
        // xoá, nên xoá khỏi CSDL cho khớp đúng thứ họ nhìn thấy trên màn hình.
        SiteText::where('group', $group)->delete();

        foreach (array_values($data['items']) as $i => $item) {
            SiteText::create([
                'group' => $group,
                'value' => $item['value'],
                'sort_order' => $i,
                'status' => true,
                'starts_at' => $item['starts_at'] ?? null,
                'ends_at' => $item['ends_at'] ?? null,
            ]);
        }

        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã lưu ' . count($data['items']) . ' dòng chữ chạy.']);
    }

    // ── Tiêu đề các khối ─────────────────────────────────────────────

    public function saveHeadings(Request $request)
    {
        $data = $request->validate([
            'headings' => ['present', 'array'],
            'headings.*' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($data['headings'] as $key => $value) {
            // Bỏ qua khoá lạ gửi lên, chỉ nhận đúng những tiêu đề đã khai.
            if (!array_key_exists($key, SiteText::HEADINGS)) {
                continue;
            }

            $macDinh = SiteText::HEADINGS[$key][0];
            $value = trim((string) $value);

            // Để trống hoặc gõ đúng chữ mặc định thì xoá dòng, khỏi lưu thừa.
            if ($value === '' || $value === $macDinh) {
                SiteText::heading()->where('key', $key)->delete();
                continue;
            }

            SiteText::updateOrCreate(
                ['group' => SiteText::GROUP_HEADING, 'key' => $key],
                ['value' => $value, 'status' => true],
            );
        }

        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã lưu tiêu đề.']);
    }
}
