<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Cấu hình vận hành lưu theo cặp khoá - giá trị JSON.
 *
 * Đọc luôn đi qua mảng mặc định ở dưới, nên thêm một tuỳ chọn mới chỉ là thêm
 * một khoá vào đó: bản ghi cũ trong CSDL thiếu khoá vẫn chạy được, không cần
 * migration và không có màn hình nào vỡ vì đọc phải null.
 */
class Setting extends Model
{
    public const SALES = 'sales';

    /** Ai chịu phí giao hàng. */
    public const PAYER_CUSTOMER = 'customer';
    public const PAYER_SHOP = 'shop';

    protected $fillable = ['key', 'value'];

    protected $casts = ['value' => 'array'];

    /**
     * Cài đặt bán hàng mặc định.
     *
     * Cố ý miễn phí giao hàng cho cả hai hình thức: trước khi có màn hình cài
     * đặt, phí giao hàng là hằng số 0 trong mã nguồn. Giữ nguyên con số đó để
     * bản cập nhật không tự dưng tính thêm tiền của khách.
     */
    public static function salesDefaults(): array
    {
        $method = [
            'enabled' => true,
            'free_shipping' => true,
            'shipping_fee' => 0,
            'fee_payer' => self::PAYER_CUSTOMER,
            // Mua từ ngần này món trở lên thì miễn phí giao hàng. null = không áp dụng.
            'free_shipping_min_items' => null,
        ];

        return [
            'bank_transfer' => $method,
            'cod' => $method,
        ];
    }

    /** Cài đặt bán hàng đã hoà với mặc định, luôn đủ khoá. */
    public static function sales(): array
    {
        $stored = (array) (static::where('key', self::SALES)->value('value') ?? []);
        $settings = [];

        foreach (static::salesDefaults() as $method => $defaults) {
            $settings[$method] = array_merge($defaults, (array) ($stored[$method] ?? []));
        }

        return $settings;
    }

    public static function putSales(array $settings): void
    {
        static::updateOrCreate(['key' => self::SALES], ['value' => $settings]);
    }

    /**
     * Phí giao hàng khách phải trả cho một đơn, tính bằng đồng.
     *
     * Trả về 0 khi shop chịu phí: đó là tiền shop bỏ ra, không được cộng vào
     * hoá đơn của khách. Muốn biết shop tốn bao nhiêu thì dùng shopShippingCost().
     */
    public static function shippingFeeFor(string $method, int $itemCount, ?array $settings = null): int
    {
        $config = ($settings ?? static::sales())[$method] ?? null;

        if (!$config || $config['fee_payer'] === self::PAYER_SHOP) {
            return 0;
        }

        return static::rawShippingFee($config, $itemCount);
    }

    /** Phần phí giao hàng shop tự gánh, để còn tính đúng lãi. */
    public static function shopShippingCost(string $method, int $itemCount, ?array $settings = null): int
    {
        $config = ($settings ?? static::sales())[$method] ?? null;

        if (!$config || $config['fee_payer'] !== self::PAYER_SHOP) {
            return 0;
        }

        return static::rawShippingFee($config, $itemCount);
    }

    /** Phí giao hàng trước khi xét ai là người trả. */
    private static function rawShippingFee(array $config, int $itemCount): int
    {
        if (!empty($config['free_shipping'])) {
            return 0;
        }

        $threshold = $config['free_shipping_min_items'];
        if ($threshold !== null && $itemCount >= (int) $threshold) {
            return 0;
        }

        return max(0, (int) $config['shipping_fee']);
    }
}
