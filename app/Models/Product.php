<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'categories_id',
        'product_name',
        'slug',
        'barcode',
        'description',
        'material',
        'brand',
        'unit',
        'import_price',
        'sell_price',
        'discount_price',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'import_price' => 'integer',
        'sell_price' => 'integer',
        'discount_price' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id')->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(Categories::class, 'categories_id')->withTrashed();
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
