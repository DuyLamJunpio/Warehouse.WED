<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Biến thể sản phẩm (size x màu) - đơn vị giữ tồn kho.
 */
class ProductVariant extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        'is_paused',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_override' => 'integer',
        'sort_order' => 'integer',
        'is_paused' => 'boolean',
    ];

    protected $appends = ['label'];

    public function product()
    {
        // Luồng bán/kho không được dùng biến thể của sản phẩm đã vào thùng
        // rác. Các dòng lịch sử dùng ProductInvoice::product()->withTrashed().
        return $this->belongsTo(Product::class);
    }

    public function style()
    {
        return $this->belongsTo(ProductStyle::class, 'product_style_id');
    }

    public function productInvoices()
    {
        return $this->hasMany(ProductInvoice::class, 'variant_id');
    }

    /** Các thành phần bị trừ khi bán một đơn vị biến thể combo này. */
    public function comboComponents()
    {
        return $this->hasMany(ProductComboItem::class, 'combo_variant_id');
    }

    /** Những combo đang dùng biến thể này làm thành phần. */
    public function usedInCombos()
    {
        return $this->hasMany(ProductComboItem::class, 'component_variant_id');
    }

    /**
     * Nhãn hiển thị: "Trống đồng / Đen / M". Bỏ qua phần rỗng.
     */
    public function getLabelAttribute(): string
    {
        if ($this->product?->is_simple) {
            return 'Sản phẩm đơn';
        }

        $label = implode(' / ', array_filter([$this->style?->name, $this->color, $this->size]));

        if ($label !== '') {
            return $label;
        }

        return $this->product?->is_simple ? 'Sản phẩm đơn' : 'Mặc định';
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
