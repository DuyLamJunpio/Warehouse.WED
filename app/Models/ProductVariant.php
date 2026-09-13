<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Biến thể sản phẩm (size x màu) - đơn vị giữ tồn kho.
 */
class ProductVariant extends Model
{
    use HasFactory;

    /** Nhãn biến thể ở đơn hàng/kho luôn có sẵn tên mẫu, không phát sinh N+1. */
    protected $with = ['style'];

    protected $fillable = [
        'product_id',
        'product_style_id',
        'size',
        'color',
        'sku',
        'quantity',
        'price_override',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_override' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $appends = ['label'];

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function style()
    {
        return $this->belongsTo(ProductStyle::class, 'product_style_id');
    }

    public function productInvoices()
    {
        return $this->hasMany(ProductInvoice::class, 'variant_id');
    }

    /**
     * Nhãn hiển thị: "Trống đồng / Đen / M". Bỏ qua phần rỗng.
     */
    public function getLabelAttribute(): string
    {
        return implode(' / ', array_filter([$this->style?->name, $this->color, $this->size])) ?: 'Mặc định';
    }

    /**
     * Giá bán thực tế của biến thể: ưu tiên giá riêng, sau đó giá khuyến mãi,
     * cuối cùng là giá bán gốc của sản phẩm.
     */
    public function getSellingPriceAttribute(): int
    {
        if ($this->price_override !== null) {
            return (int) $this->price_override;
        }

        $product = $this->product;

        if (!$product) {
            return 0;
        }

        return (int) ($product->discount_price ?? $product->sell_price);
    }
}
