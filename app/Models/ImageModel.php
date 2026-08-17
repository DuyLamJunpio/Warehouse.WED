<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Media của sản phẩm: ảnh hoặc video.
 */
class ImageModel extends Model
{
    use HasFactory;

    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';

    protected $fillable = [
        'product_id',
        'path',
        'name',
        'media_type',
        'sort_order',
        'is_pined',
    ];

    protected $casts = [
        'is_pined' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function isVideo(): bool
    {
        return $this->media_type === self::TYPE_VIDEO;
    }

    public function scopeImages($query)
    {
        return $query->where('media_type', self::TYPE_IMAGE);
    }
}
