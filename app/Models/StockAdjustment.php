<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Một lần điều chỉnh tồn kho thủ công của một biến thể.
 */
class StockAdjustment extends Model
{
    use HasFactory;

    /** Lý do điều chỉnh - key lưu DB, value hiển thị cho người dùng. */
    public const REASONS = [
        'kiem_ke' => 'Kiểm kê lại',
        'hang_loi' => 'Hàng lỗi / hỏng',
        'that_thoat' => 'Thất thoát / mất hàng',
        'tra_hang' => 'Khách trả hàng',
        'khuyen_mai' => 'Xuất tặng / khuyến mãi',
        'khac' => 'Lý do khác',
    ];

    protected $fillable = [
        'variant_id',
        'user_id',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'reason',
        'note',
    ];

    protected $casts = [
        'quantity_before' => 'integer',
        'quantity_change' => 'integer',
        'quantity_after' => 'integer',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getReasonLabelAttribute(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }
}
