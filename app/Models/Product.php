<?php

namespace App\Models;

use App\Support\ProductPricing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Nhãn hiển thị của ba cấp biến thể. Dữ liệu tồn kho cũ vẫn giữ ở các cột
     * style/color/size; nhãn này chỉ giúp mỗi ngành gọi chúng đúng ngữ cảnh.
     */
    public const DEFAULT_VARIANT_ATTRIBUTE_LABELS = [
        'style' => 'Phiên bản',
        'color' => 'Lựa chọn 1',
        'size' => 'Lựa chọn 2',
    ];

    protected $fillable = [
        'supplier_id',
        'categories_id',
        'product_name',
        'slug',
        'barcode',
        'description',
        'material',
        'brand',
        'audience',
        'unit',
        'variant_attribute_labels',
        'import_price',
        'sell_price',
        'discount_price',
        'is_featured',
        'manage_stock',
        'is_combo',
        'status',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'manage_stock' => 'boolean',
        'is_combo' => 'boolean',
        'import_price' => 'integer',
        'sell_price' => 'integer',
        'discount_price' => 'integer',
        'variant_attribute_labels' => 'array',
    ];

    protected $dates = ['deleted_at'];

    /** Chuẩn hóa nhãn do quản trị viên tự đặt trước khi lưu hoặc trả về API. */
    public static function normalizeVariantAttributeLabels(?array $labels): array
    {
        $normalized = [];

        foreach (self::DEFAULT_VARIANT_ATTRIBUTE_LABELS as $key => $fallback) {
            $value = preg_replace('/\s+/u', ' ', trim((string) ($labels[$key] ?? '')));
            $normalized[$key] = mb_substr($value ?: $fallback, 0, 40, 'UTF-8');
        }

        return $normalized;
    }

    /** Percentage discount derived from the stored sale price, for storefront display. */
    public function getDiscountPercentAttribute(): ?float
    {
        return ProductPricing::discountPercent(
            $this->sell_price === null ? null : (int) $this->sell_price,
            $this->discount_price === null ? null : (int) $this->discount_price,
        );
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id')->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(Categories::class, 'categories_id')->withTrashed();
    }

    /**
     * Sản phẩm được phép xuất hiện ở web bán hàng.
     *
     * Trạng thái danh mục là công tắc hiển thị của cả nhóm sản phẩm: khi một
     * danh mục chuyển sang "ngưng sử dụng", sản phẩm trong đó không được lọt
     * vào catalogue, trang chi tiết hay các khối trên trang chủ. Sản phẩm cũ
     * chưa xếp danh mục vẫn được giữ khả năng hiển thị để không tự làm biến mất
     * dữ liệu legacy chỉ vì thiếu liên kết danh mục.
     */
    public function scopeStorefrontVisible(Builder $query): Builder
    {
        return $query
            ->where('products.status', '!=', 0)
            ->where(function (Builder $products): void {
                $products->whereNull('products.categories_id')
                    ->orWhereHas('category', function (Builder $category): void {
                        // `category()` dùng withTrashed cho màn quản trị, nên
                        // web bán hàng phải chủ động loại cả danh mục đã xoá mềm.
                        $category->where('categories.status', 1)
                            ->whereNull('categories.deleted_at');
                    });
            });
    }

    public function productInvoices()
    {
        return $this->hasMany(ProductInvoice::class);
    }

    public function productImage()
    {
        return $this->hasMany(ImageModel::class, 'product_id', 'id')->orderBy('sort_order');
    }

    public function imageModel()
    {
        return $this->hasMany(ImageModel::class);
    }

    public function location()
    {
        return $this->hasOne(ProductLocation::class);
    }

    /**
     * Biến thể size/màu - nơi giữ tồn kho.
     * Thay cho quan hệ expiries() của hệ thống kho cũ.
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'id')->orderBy('sort_order');
    }

    /** Các mẫu hình/kiểu; mỗi mẫu giữ một ảnh dùng chung cho mọi màu và size. */
    public function styles()
    {
        return $this->hasMany(ProductStyle::class, 'product_id', 'id')->orderBy('sort_order');
    }

    /**
     * Ảnh đại diện: ảnh được ghim, nếu không có thì lấy ảnh đầu tiên.
     */
    public function getThumbnailAttribute(): ?ImageModel
    {
        return $this->productImage->firstWhere('is_pined', true)
            ?? $this->productImage->firstWhere('media_type', 'image')
            ?? $this->productImage->first();
    }
}
