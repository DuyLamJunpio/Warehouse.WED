<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Một bảng giá đã xuất bản — bất biến kể từ lúc tạo.
 *
 * Không có hàm sửa nào ở đây, và đó là chủ đích. Chủ shop sửa giá lúc 3 giờ
 * chiều thì đơn đặt lúc 2 giờ đang chờ duyệt không được đổi tiền; muốn đổi giá
 * thì xuất bản một phiên bản mới, đơn mới đi theo bản mới.
 *
 * `data` tự đủ: nó chụp cả danh sách kỹ thuật và bậc khổ tại thời điểm xuất
 * bản, nên tính lại một đơn cũ không cần đọc bảng nào khác.
 */
class PrintPricingVersion extends Model
{
    protected $fillable = ['data', 'note', 'published_by', 'published_at'];

    protected $casts = [
        'data' => 'array',
        'published_at' => 'datetime',
    ];

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /** Bản đang có hiệu lực; null khi chưa xuất bản lần nào. */
    public static function latestPublished(): ?self
    {
        return static::query()->orderByDesc('published_at')->orderByDesc('id')->first();
    }
}
