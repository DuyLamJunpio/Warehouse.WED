<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phiên thanh toán VietQR của web bán hàng.
 *
 * Đây chưa phải là đơn hàng: chỉ giữ báo giá đã chốt, mã chuyển khoản và hạn
 * thanh toán. Invoice chỉ được tạo khi SePay xác nhận tiền đã vào tài khoản.
 */
class StorefrontPaymentSession extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'checkout_ref',
        'checkout_fingerprint',
        'payment_code',
        'payload',
        'total_amount',
        'status',
        'expires_at',
        'paid_at',
        'invoice_id',
    ];

    protected $casts = [
        'payload' => 'array',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function hasExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            || ($this->status === self::STATUS_PENDING && $this->expires_at?->lte(now()));
    }
}
