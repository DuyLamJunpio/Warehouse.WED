<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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

    protected static function booted(): void
    {
        // Chuẩn hóa ngay cả những nơi tạo khách không đi qua controller (seeder,
        // API cũ, import dữ liệu), để về sau mọi phép so sánh dùng cùng một dạng.
        static::saving(function (self $customer) {
            $phone = self::normalizePhone($customer->customer_phone);
            if ($phone !== null) {
                $customer->customer_phone = $phone;
            }
            $customer->customer_email = self::normalizeEmail($customer->customer_email);
        });
    }

    /**
     * Đưa số điện thoại Việt Nam về dạng nội địa 0xxxxxxxxx.
     *
     * Ví dụ: 0862850761, 84862850761, +84862850761 và 0084862850761
     * đều trở thành 0862850761.
     */
    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', trim((string) $phone)) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0084')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '84')) {
            $local = substr($digits, 2);
            // Chỉ đổi mã quốc gia khi phần số thuê bao có đúng 9 chữ số,
            // tránh biến nhầm số quốc tế/đầu số không phải của Việt Nam.
            if (str_starts_with($local, '0')) {
                $local = substr($local, 1);
            }
            if (strlen($local) === 9) {
                $digits = '0' . $local;
            }
        }

        return $digits;
    }

    public static function normalizeEmail(?string $email): ?string
    {
        $email = mb_strtolower(trim((string) $email));

        return $email === '' ? null : $email;
    }

    /**
     * Tìm hồ sơ khác có cùng số điện thoại hoặc email sau khi chuẩn hóa.
     * Dùng cho form sửa để không tạo thêm hồ sơ thứ hai do nhập khác định dạng.
     */
    public static function findDuplicate(?string $phone, ?string $email, ?int $ignoreId = null): ?self
    {
        $phone = self::normalizePhone($phone);
        $email = self::normalizeEmail($email);

        if ($phone === null && $email === null) {
            return null;
        }

        return self::query()
            ->when($ignoreId !== null, fn($query) => $query->whereKeyNot($ignoreId))
            ->orderBy('id')
            ->get(['id', 'customer_phone', 'customer_email'])
            ->first(fn(self $customer) =>
                ($phone !== null && self::normalizePhone($customer->customer_phone) === $phone)
                || ($email !== null && self::normalizeEmail($customer->customer_email) === $email)
            );
    }
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
        return self::mergeByIdentity($data);
    }

    /**
     * Tìm hoặc gộp khách theo số điện thoại/email đã chuẩn hóa.
     * Nếu dữ liệu cũ đã có nhiều hồ sơ trùng, hóa đơn của các hồ sơ phụ được
     * chuyển về hồ sơ đầu tiên rồi hồ sơ phụ được soft-delete.
     */
    public static function mergeByIdentity(array $data): self
    {
        $phone = self::normalizePhone($data['customer_phone'] ?? null);
        $email = self::normalizeEmail($data['customer_email'] ?? null);

        if ($phone === null) {
            throw new \InvalidArgumentException('Số điện thoại khách hàng không hợp lệ.');
        }

        $matches = self::query()
            ->orderBy('id')
            ->get()
            ->filter(fn(self $customer) =>
                ($phone !== null && self::normalizePhone($customer->customer_phone) === $phone)
                || ($email !== null && self::normalizeEmail($customer->customer_email) === $email)
            )
            ->values();

        $customer = $matches->first() ?? new self();
        $hasExistingCustomer = $matches->isNotEmpty();

        foreach ($matches->skip(1) as $duplicate) {
            foreach (['customer_name', 'customer_email', 'address', 'province', 'ward', 'note', 'avatar'] as $field) {
                if (blank($customer->{$field}) && filled($duplicate->{$field})) {
                    $customer->{$field} = $duplicate->{$field};
                }
            }

            // Giữ nguyên toàn bộ lịch sử mua hàng sau khi gộp.
            DB::table('invoices')
                ->where('customer_id', $duplicate->id)
                ->update(['customer_id' => $customer->id]);

            $duplicate->delete();
        }

        // Dữ liệu mới từ lần đặt gần nhất được ưu tiên, nhưng không để một
        // lần đặt thiếu email/địa chỉ xóa thông tin tốt hơn đang có.
        foreach ($data as $field => $value) {
            if ($hasExistingCustomer && $field === 'status') {
                continue;
            }
            if ($field === 'customer_phone' || $field === 'customer_email') {
                continue;
            }
            if ($value !== null && $value !== '') {
                $customer->{$field} = $value;
            }
        }
        if ($phone !== null) {
            $customer->customer_phone = $phone;
        }
        if ($email !== null) {
            $customer->customer_email = $email;
        }
        $customer->status = $customer->status ?? 0;
        $customer->save();

        return $customer;
    }
}
