<?php

namespace App\Services;

/**
 * Bốn vị trí in trên áo — HẰNG SỐ, không phải dữ liệu.
 *
 * Trước đây mỗi phôi có một bộ "vùng in" do chủ shop tự kéo khung trên ảnh
 * mockup. Nghe thì linh hoạt, thực tế là bắt người quản trị làm cái việc mà
 * chính khách mới biết mình muốn: khách in ngực trái hay in giữa ngực, in to
 * hay in nhỏ, là quyết định lúc thiết kế chứ không phải lúc khai phôi. Khai
 * trước là vừa thừa việc cho chủ shop, vừa nhốt khách vào một cái khung.
 *
 * Nên bây giờ chỉ còn bốn chỗ có thật trên chiếc áo. Khách chọn một trong bốn,
 * rồi kéo hình đi đâu tuỳ ý trong chỗ đó và phóng to nhỏ theo ý mình. Tiền in
 * đi theo kích thước khách kéo — xem App\Services\PrintPricing.
 *
 * ─── Vì sao vẫn có giới hạn mm ───────────────────────────────────────
 *
 * `max_width_mm` / `max_height_mm` KHÔNG phải là khung in quay lại dưới tên
 * khác: khách vẫn đặt hình ở bất cứ đâu và to nhỏ tuỳ ý bên trong. Nó là giới
 * hạn vật lý của xưởng — không ai ép được tờ decal A3 lên bả vai áo. Ghi cứng
 * ở đây thay vì cho cấu hình vì nó là chuyện của cái máy in, không phải chuyện
 * của từng chiếc phôi.
 *
 * ─── `views` là danh sách lùi dần ────────────────────────────────────
 *
 * Vị trí nào cũng cần một tấm mockup để khách nhìn. Mặt sau muốn tấm chụp lưng,
 * không có thì lùi về tấm mặt trước — hiện sai góc vẫn hơn hiện ô trống, và mm
 * thì không phụ thuộc tấm ảnh nào cả.
 *
 * Bên web bán hàng KHÔNG chép lại bảng này. Nó đi theo `catalogue` sang đó, nên
 * sửa số ở đây là hai bên đổi cùng lúc.
 */
final class PrintPositions
{
    public const FRONT = 'front';
    public const BACK = 'back';
    public const SHOULDER_LEFT = 'shoulder_left';
    public const SHOULDER_RIGHT = 'shoulder_right';

    /**
     * Thứ tự khai ở đây là thứ tự khách nhìn thấy trên web.
     *
     * @var array<string, array{label: string, views: string[], max_width_mm: int, max_height_mm: int}>
     */
    private const DEFS = [
        self::FRONT => [
            'label' => 'Mặt trước',
            'views' => ['front'],
            'max_width_mm' => 320,
            'max_height_mm' => 420,
        ],
        self::BACK => [
            'label' => 'Mặt sau',
            'views' => ['back', 'front'],
            'max_width_mm' => 320,
            'max_height_mm' => 420,
        ],
        self::SHOULDER_LEFT => [
            'label' => 'Vai trái',
            'views' => ['sleeve', 'front'],
            'max_width_mm' => 120,
            'max_height_mm' => 120,
        ],
        self::SHOULDER_RIGHT => [
            'label' => 'Vai phải',
            'views' => ['sleeve', 'front'],
            'max_width_mm' => 120,
            'max_height_mm' => 120,
        ],
    ];

    /** @return string[] */
    public static function keys(): array
    {
        return array_keys(self::DEFS);
    }

    public static function has(?string $key): bool
    {
        return $key !== null && isset(self::DEFS[$key]);
    }

    /** Nhãn tiếng Việt; khoá lạ thì trả về chính nó thay vì rỗng. */
    public static function label(?string $key): string
    {
        return self::DEFS[$key]['label'] ?? (string) $key;
    }

    /** @return array{label: string, views: string[], max_width_mm: int, max_height_mm: int}|null */
    public static function get(?string $key): ?array
    {
        return self::DEFS[$key] ?? null;
    }

    /**
     * Lọc bỏ khoá lạ rồi xếp lại theo thứ tự chuẩn.
     *
     * Rỗng hoặc null nghĩa là phôi chưa khai gì — trả về đủ bốn vị trí. Một phôi
     * không bán được vị trí nào là một phôi không bán được, và đó gần như luôn
     * là dữ liệu thiếu chứ không phải ý của chủ shop.
     *
     * @return string[]
     */
    public static function normalise(?array $keys): array
    {
        $wanted = array_filter((array) $keys, fn ($k) => is_string($k) && isset(self::DEFS[$k]));

        if (!$wanted) {
            return self::keys();
        }

        return array_values(array_filter(self::keys(), fn ($k) => in_array($k, $wanted, true)));
    }

    /**
     * Bảng vị trí gửi sang web bán hàng.
     *
     * @return array<int, array{key: string, label: string, views: string[], max_width_mm: int, max_height_mm: int}>
     */
    public static function payload(): array
    {
        $out = [];
        foreach (self::DEFS as $key => $def) {
            $out[] = ['key' => $key] + $def;
        }

        return $out;
    }

    /**
     * Bản đồ đầu vào cho bộ máy giá, chỉ gồm những vị trí phôi này bật.
     *
     * @param string[] $keys
     * @return array<string, array{label: string, max_width_mm: int, max_height_mm: int}>
     */
    public static function pricingMap(array $keys): array
    {
        $out = [];
        foreach (self::normalise($keys) as $key) {
            $out[$key] = [
                'label' => self::DEFS[$key]['label'],
                'max_width_mm' => self::DEFS[$key]['max_width_mm'],
                'max_height_mm' => self::DEFS[$key]['max_height_mm'],
            ];
        }

        return $out;
    }
}
