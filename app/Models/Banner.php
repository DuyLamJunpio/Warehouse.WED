<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Một slide của hero section trên trang chủ web bán hàng.
 */
class Banner extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';

    protected $fillable = [
        'media_path', 'media_type', 'poster_path', 'mobile_path', 'alt',
        'heading', 'subheading', 'cta_label', 'cta_link',
        'sort_order', 'status', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Chỉ những slide đang được phép hiển thị ngay lúc này.
     * Để trống ngày bắt đầu/kết thúc nghĩa là hiện mãi.
     */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', true)
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order');
    }

    public function isVideo(): bool
    {
        return $this->media_type === self::TYPE_VIDEO;
    }

    /** Đang trong thời gian hiển thị hay không - dùng để tô màu ở trang quản trị. */
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
