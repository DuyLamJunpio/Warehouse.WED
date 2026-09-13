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
        return $this->availabilityError() === null;
    }

    /** A customer-safe explanation for why this voucher cannot be applied. */
    public function availabilityError(): ?string
    {
        $now = now();

        if (! $this->status) return 'Mã giảm giá hiện không còn áp dụng.';
        if ($this->starts_at !== null && $this->starts_at->gt($now)) return 'Mã giảm giá chưa đến thời gian áp dụng.';
        if ($this->ends_at !== null && $this->ends_at->lt($now)) return 'Mã giảm giá đã hết hạn.';
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) return 'Mã giảm giá đã hết lượt sử dụng.';

        return null;
    }

    /** Calculate a quote from Warehouse rules. All amounts are whole VND. */
    public function quote(int $subtotal, int $shipping): array
    {
        if ($error = $this->availabilityError()) return ['error' => $error];
        if ($subtotal < (int) $this->min_order_amount) {
            return ['error' => "Mã {$this->code} chỉ áp dụng cho đơn hàng từ "
                . number_format((int) $this->min_order_amount, 0, ',', '.') . '₫.'];
        }

        $discount = 0;
        $newShipping = $shipping;

        if ($this->type === self::TYPE_PERCENTAGE) {
            $discount = (int) round($subtotal * (int) $this->value / 100);
            if ($this->max_discount !== null) $discount = min($discount, (int) $this->max_discount);
            $discount = min($discount, $subtotal);
        } elseif ($this->type === self::TYPE_FIXED_AMOUNT) {
            $discount = min((int) $this->value, $subtotal);
        } elseif ($this->type === self::TYPE_FREE_SHIPPING) {
            $newShipping = 0;
        }

        return ['discount' => $discount, 'shipping' => $newShipping];
    }
}
