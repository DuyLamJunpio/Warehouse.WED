<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Một biến động tồn kho có thể kiểm toán được. */
class StockMovement extends Model
{
    use HasFactory;

    public const TYPE_SALE = 'sale';
    public const TYPE_RESTOCK = 'restock';
    public const TYPE_MANUAL = 'manual_adjustment';
    public const TYPE_IMPORT = 'import';

    public const TYPE_LABELS = [
        self::TYPE_SALE => 'Bán hàng',
        self::TYPE_RESTOCK => 'Hoàn kho',
        self::TYPE_MANUAL => 'Điều chỉnh thủ công',
        self::TYPE_IMPORT => 'Nhập kho',
    ];

    protected $fillable = [
        'variant_id',
        'invoice_id',
        'user_id',
        'type',
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

    public function invoice()
    {
        return $this->belongsTo(Invoice::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }
}
