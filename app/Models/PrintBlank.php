<?php

namespace App\Models;

use App\Services\PrintPositions;
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
        'frame_width_mm', 'frame_height_mm', 'positions', 'moq', 'lead_days',
        'template_path', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'base_price' => 'integer',
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

    /** Phôi được phép hiện trong studio in áo của web bán hàng. */
    public function scopeStorefrontVisible(Builder $query): Builder
    {
        return $query
            ->where('print_blanks.is_active', true)
            // Phôi có danh mục thì danh mục đó phải đang dùng. Phôi không xếp
            // danh mục vẫn hợp lệ vì đây là trường tuỳ chọn.
            ->where(function (Builder $blanks): void {
                $blanks->whereNull('print_blanks.categories_id')
                    ->orWhereHas('category', function (Builder $category): void {
                        $category->where('categories.status', 1)
                            ->whereNull('categories.deleted_at');
                    });
            })
            // Nếu phôi nối với một sản phẩm kho, nó cũng kế thừa trạng thái
            // hiển thị của sản phẩm và danh mục sản phẩm đó.
            ->where(function (Builder $blanks): void {
                $blanks->whereNull('print_blanks.product_id')
                    ->orWhereHas('product', fn (Builder $product) => $product->storefrontVisible());
            });
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
