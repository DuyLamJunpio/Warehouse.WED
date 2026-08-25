<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Phôi in — chiếc áo trắng trước khi có hình lên nó.
 *
 * `product_id` CÓ THỂ RỖNG, và đó là lựa chọn chính của thiết kế này: hầu hết
 * shop in áo đặt phôi từ nhà cung cấp và không đếm tồn theo từng màu × size.
 * Nối vào sản phẩm trong kho là tính năng thêm cho ai có trữ phôi sẵn — nối rồi
 * thì giá và tồn kho thừa hưởng từ bên kia thay vì khai lại ở đây.
 */
class PrintBlank extends Model
{
    protected $fillable = [
        'product_id', 'name', 'slug', 'description', 'base_price',
        'frame_width_mm', 'frame_height_mm', 'moq', 'lead_days',
        'template_path', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'base_price' => 'integer',
        'frame_width_mm' => 'integer',
        'frame_height_mm' => 'integer',
        'moq' => 'integer',
        'lead_days' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function colors(): HasMany
    {
        return $this->hasMany(PrintBlankColor::class)->orderBy('sort_order')->orderBy('id');
    }

    public function zones(): HasMany
    {
        return $this->hasMany(PrintZone::class)->orderBy('sort_order')->orderBy('id');
    }

    public function mockups(): HasMany
    {
        return $this->hasMany(PrintMockup::class);
    }

    public function techniques(): BelongsToMany
    {
        return $this->belongsToMany(PrintTechnique::class, 'print_blank_technique');
    }

    /**
     * Giá phôi thật sự dùng để tính tiền.
     *
     * Nối kho thì sản phẩm bên đó mới là nguồn: giá khuyến mãi trước, rồi giá
     * bán. Khai riêng ở hai chỗ là sớm muộn hai chỗ lệch nhau.
     */
    public function effectiveBasePrice(): int
    {
        if ($this->product) {
            return (int) ($this->product->discount_price ?? $this->product->sell_price ?? 0);
        }

        return (int) $this->base_price;
    }

    /**
     * Size bán được và phụ thu của từng size.
     *
     * Nối kho thì size lấy từ biến thể thật, và phụ thu suy ra từ chênh lệch
     * giữa giá riêng của biến thể với giá phôi — chủ shop không phải khai lại.
     */
    public function sizeMap(): array
    {
        if (!$this->product) {
            return [];
        }

        $base = $this->effectiveBasePrice();
        $sizes = [];

        foreach ($this->product->variants ?? [] as $variant) {
            $size = $variant->size;
            if (!$size || isset($sizes[$size])) {
                continue;
            }
            $price = (int) ($variant->price_override ?? $base);
            $sizes[$size] = max(0, $price - $base);
        }

        return $sizes;
    }
}
