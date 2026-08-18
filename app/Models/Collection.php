<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Bộ sưu tập trên trang chủ, sản phẩm do chủ shop tự tích.
 */
class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'subtitle', 'image_path', 'cta_label', 'cta_link',
        'sort_order', 'status', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'collection_product')
            ->withPivot('sort_order')
            ->orderBy('collection_product.sort_order');
    }

    /** Chỉ bộ sưu tập đang trong thời gian hiển thị. */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', true)
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order');
    }

    public function getIsLiveAttribute(): bool
    {
        if (!$this->status) {
            return false;
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }
        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }
        return true;
    }
}
