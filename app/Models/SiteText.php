<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Dải thông báo trên cùng và tiêu đề các khối trên trang chủ.
 */
class SiteText extends Model
{
    use HasFactory;

    /** Dải chữ nhỏ chạy trên cùng, hiện ở MỌI trang chứ không riêng trang chủ. */
    public const GROUP_ANNOUNCEMENT = 'announcement';
    public const GROUP_HEADING = 'heading';

    /**
     * Các tiêu đề chỉnh được, kèm chữ mặc định.
     *
     * Chưa ai sửa thì web dùng chữ mặc định này, nên trang không bao giờ trống
     * tiêu đề dù bảng chưa có dòng nào.
     */
    public const HEADINGS = [
        'new_arrivals.title' => ['Hàng mới đã về', 'Tiêu đề khối hàng mới'],
        'seasonal.title' => ['Bộ sưu tập theo mùa', 'Tiêu đề khối bộ sưu tập'],
        'categories.title' => ['Mua theo danh mục', 'Tiêu đề khối danh mục'],
        'categories.subtitle' => ['Chọn theo nhóm đồ bạn đang tìm.', 'Mô tả dưới tiêu đề danh mục'],
        'best_sellers.title' => ['Bán chạy nhất', 'Tiêu đề khối bán chạy'],
        'best_sellers.subtitle' => ['Những mẫu được khách chọn nhiều nhất.', 'Mô tả dưới tiêu đề bán chạy'],
    ];

    protected $fillable = ['group', 'key', 'value', 'sort_order', 'status', 'starts_at', 'ends_at'];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /** Chỉ những dòng đang được phép hiển thị ngay lúc này. */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', true)
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order');
    }

    public function scopeHeading(Builder $query): Builder
    {
        return $query->where('group', self::GROUP_HEADING);
    }

    public function scopeAnnouncement(Builder $query): Builder
    {
        return $query->where('group', self::GROUP_ANNOUNCEMENT);
    }
}
