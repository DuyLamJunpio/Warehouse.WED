<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;
    /**
     * Hạng khách tính tự động theo tổng chi tiêu (đồng), không lưu vào DB
     * để không bao giờ lệch với dữ liệu đơn hàng thật.
     */
    public const TIER_VIP = 5000000;
    public const TIER_LOYAL = 1000000;

    protected $fillable = [
        'avatar',
        'customer_name',
        'customer_phone',
        'customer_email',
        'address',
        'province',
        'ward',
        'note',
        'status',
    ];
    protected $appends = ['total_invoices'];

    protected $dates = ['deleted_at'];
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Getter cho tổng số hóa đơn
    public function getTotalInvoicesAttribute()
    {
        return $this->invoices()->count();
    }
    public function invoicesPaid()
    {
        return $this->hasMany(Invoice::class)->where('pay_status', 1);
    }

    /**
     * Mối quan hệ với hóa đơn còn nợ.
     */
    public function invoicesOwed()
    {
        return $this->hasMany(Invoice::class)->where('pay_status', 0);
    }

    /** Đơn hàng bán của khách, mới nhất trước. */
    public function orders()
    {
        return $this->hasMany(Invoice::class)
            ->where('invoice_type', Invoice::TYPE_ORDER)
            ->latest();
    }

    /**
     * Địa chỉ giao hàng đầy đủ: số nhà, phường/xã, tỉnh/thành phố.
     */
    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([$this->address, $this->ward, $this->province]))
            ?: 'Chưa có địa chỉ';
    }

    /**
     * Tổng chi tiêu: chỉ tính đơn đã hoàn thành, vì đơn đang giao có thể bị hoàn.
     */
    public function getTotalSpentAttribute(): int
    {
        return (int) ($this->orders_completed_sum_total_amount
            ?? $this->orders()->where('order_status', Invoice::STATUS_COMPLETED)->sum('total_amount'));
    }

    public function getTierAttribute(): string
    {
        $spent = $this->total_spent;

        if ($spent >= self::TIER_VIP) {
            return 'VIP';
        }

        return $spent >= self::TIER_LOYAL ? 'Thân thiết' : 'Khách mới';
    }

    /**
     * Tìm khách theo số điện thoại, chưa có thì tạo mới.
     * Khách web không có tài khoản nên số điện thoại là thứ dùng để nhận diện;
     * tên và địa chỉ luôn lấy theo lần đặt gần nhất.
     */
    public static function mergeByPhone(array $data): self
    {
        $phone = preg_replace('/\D/', '', $data['customer_phone'] ?? '');

        $customer = self::where('customer_phone', $phone)->first() ?? new self();
        $customer->fill($data);
        $customer->customer_phone = $phone;
        $customer->status = $customer->status ?? 0;
        $customer->save();

        return $customer;
    }
}
