<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Một mẫu hình/kiểu của sản phẩm. Ảnh được giữ một lần ở đây rồi mọi biến thể
 * màu/size bên dưới cùng tham chiếu, thay vì nhân bản file theo từng SKU.
 */
class ProductStyle extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_model_id',
        'name',
        'name_key',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function image()
    {
        return $this->belongsTo(ImageModel::class, 'image_model_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_style_id')->orderBy('sort_order');
    }
}
