<?php

namespace App\Models;

use App\Services\PrintPositions;
use App\Services\PrintPricing;
use Illuminate\Database\Eloquent\Builder;
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
        'product_id', 'categories_id', 'name', 'slug', 'description', 'base_price',
        'discount_type', 'discount_value', 'frame_width_mm', 'frame_height_mm', 'positions', 'moq', 'lead_days',
        'template_path', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'base_price' => 'integer',
        'discount_value' => 'integer',
        'frame_width_mm' => 'integer',
        'frame_height_mm' => 'integer',
        'positions' => 'array',
        'moq' => 'integer',
        'lead_days' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Danh mục phôi — dùng chung bảng `categories` với hàng bán sẵn.
     *
     * `withTrashed` cùng lý do như Product::category(): danh mục xoá mềm rồi thì
     * thẻ phôi bên trang quản trị vẫn phải đọc được tên nó, chứ không hiện một ô
     * trống bí hiểm. Bên web bán hàng thì chỉ danh mục còn sống mới thành chip lọc.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'categories_id')->withTrashed();
    }

    /**
     * Phôi được phép hiện trong studio in áo của web bán hàng.
     *
     * `categories_id` ở phôi chỉ dùng để nhóm chip lọc trong studio, không phải
     * công tắc bán hàng. Nếu dùng trạng thái danh mục ở đây, tắt một danh mục
     * hàng bán sẵn có thể vô tình làm biến mất toàn bộ phôi in. Phôi có công
     * tắc riêng `is_active` và đó là nguồn quyết định duy nhất tại storefront.
     */
    public function scopeStorefrontVisible(Builder $query): Builder
    {
        return $query->where('print_blanks.is_active', true);
    }

    public function colors(): HasMany
    {
        return $this->hasMany(PrintBlankColor::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Bốn vị trí in phôi này bán được, đã lọc và xếp theo thứ tự chuẩn.
     *
     * Cột `positions` để trống nghĩa là chưa ai tick gì — hiểu là đủ bốn, vì một
     * phôi không bán được vị trí nào thì không phải là một phôi.
     *
     * @return string[]
     */
    public function positionKeys(): array
    {
        return PrintPositions::normalise($this->positions);
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
     * Số đồng giảm cho một áo có tổng "phôi + tiền in" là `$subtotal`.
     *
     * Phôi nối kho mà sản phẩm bên kia đang có giá khuyến mãi thì mức giảm này
     * CỘNG DỒN lên giá khuyến mãi đó — đúng như ô nhập ở trang quản trị nói.
     */
    public function discountFor(int|float $subtotal): int
    {
        return PrintPricing::blankDiscount($this->discount_type, $this->discount_value, (float) $subtotal);
    }

    /** Nhãn ngắn cho mức giảm, ví dụ "−10%"; null khi phôi không giảm giá. */
    public function discountLabel(): ?string
    {
        return PrintPricing::discountLabel($this->discount_type, $this->discount_value);
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

    /**
     * Size thực sự đặt được cho một màu. Màu chưa khai size riêng kế thừa toàn
     * bộ size sản phẩm nối kho; phôi độc lập thì luôn có "Một cỡ".
     *
     * @return string[]
     */
    public function sizesForColor(PrintBlankColor $color): array
    {
        $sizes = array_values(array_filter(
            $color->sizes ?? [],
            fn ($size) => is_string($size) && trim($size) !== '',
        ));

        return $sizes ?: (array_keys($this->sizeMap()) ?: ['Một cỡ']);
    }
}
