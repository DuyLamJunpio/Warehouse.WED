<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED_AMOUNT = 'fixed_amount';
    public const TYPE_FREE_SHIPPING = 'free_shipping';

    public const TYPES = [
        self::TYPE_PERCENTAGE => 'Giảm theo phần trăm',
        self::TYPE_FIXED_AMOUNT => 'Giảm số tiền cố định',
        self::TYPE_FREE_SHIPPING => 'Miễn phí vận chuyển',
    ];

    protected $fillable = [
        'code', 'name', 'type', 'value', 'max_discount', 'min_order_amount',
        'usage_limit', 'used_count', 'starts_at', 'ends_at', 'status', 'description',
    ];

    protected $casts = [
        'value' => 'integer',
        'max_discount' => 'integer',
        'min_order_amount' => 'integer',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'status' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getIsCurrentlyAvailableAttribute(): bool
    {
        $now = now();

        return $this->status
            && ($this->starts_at === null || $this->starts_at->lte($now))
            && ($this->ends_at === null || $this->ends_at->gte($now))
            && ($this->usage_limit === null || $this->used_count < $this->usage_limit);
    }
}
