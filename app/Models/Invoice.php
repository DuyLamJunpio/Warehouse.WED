<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    /** invoice_type */
    public const TYPE_IMPORT = 0; // phiếu nhập hàng từ nhà cung cấp
    public const TYPE_ORDER  = 1; // đơn hàng bán cho khách

    /** order_status */
    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PACKING   = 'packing';
    public const STATUS_SHIPPING  = 'shipping';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_RETURNED  = 'returned';

    public const ORDER_STATUSES = [
        self::STATUS_PENDING   => 'Chờ xác nhận',
        self::STATUS_CONFIRMED => 'Đã xác nhận',
        self::STATUS_PACKING   => 'Đang đóng gói',
        self::STATUS_SHIPPING  => 'Đang giao',
        self::STATUS_COMPLETED => 'Hoàn thành',
        self::STATUS_CANCELLED => 'Đã hủy',
        self::STATUS_RETURNED  => 'Hoàn hàng',
    ];

    /**
     * Luồng trạng thái đơn hàng: từ trạng thái này được chuyển sang những trạng thái nào.
     * Đã hủy và hoàn hàng là điểm cuối, không quay ngược lại được - nhờ vậy
     * hàng chỉ được cộng trả về kho đúng một lần.
     */
    public const STATUS_TRANSITIONS = [
        self::STATUS_PENDING   => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_PACKING, self::STATUS_CANCELLED],
        self::STATUS_PACKING   => [self::STATUS_SHIPPING, self::STATUS_CANCELLED],
        self::STATUS_SHIPPING  => [self::STATUS_COMPLETED, self::STATUS_RETURNED],
        self::STATUS_COMPLETED => [self::STATUS_RETURNED],
        self::STATUS_CANCELLED => [],
        self::STATUS_RETURNED  => [],
    ];

    /** Chuyển sang các trạng thái này thì phải cộng trả hàng về kho. */
    public const STATUS_RESTOCK = [self::STATUS_CANCELLED, self::STATUS_RETURNED];

    protected $fillable = [
        'user_id',
        'total_amount',
        'invoice_type',
        'customer_id',
        'supplier_id',
        'pay_status',
        'due_date',
        'note',
        'term',
        'discount',
        'signature_name',
        'signature',
        'order_code',
        'order_status',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'shipping_fee',
        'payment_method',
    ];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function productInvoices()
    {
        return $this->hasMany(ProductInvoice::class);
    }

    /** Chỉ đơn hàng bán. */
    public function scopeOrders($query)
    {
        return $query->where('invoice_type', self::TYPE_ORDER);
    }

    /** Chỉ phiếu nhập hàng. */
    public function scopeImports($query)
    {
        return $query->where('invoice_type', self::TYPE_IMPORT);
    }

    public function getOrderStatusLabelAttribute(): ?string
    {
        return self::ORDER_STATUSES[$this->order_status] ?? null;
    }

    /** Các trạng thái kế tiếp hợp lệ của đơn này. */
    public function getNextStatusesAttribute(): array
    {
        return self::STATUS_TRANSITIONS[$this->order_status] ?? [];
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, $this->next_statuses, true);
    }

    /** Tổng tiền hàng chưa tính phí ship và chiết khấu. */
    public function getSubtotalAttribute(): int
    {
        return (int) $this->productInvoices->sum(fn($line) => $line->line_total);
    }

    public static function searchByInvoiceId($invoiceId)
    {
        return self::where('id', $invoiceId)
            ->with([
                'user' => function ($query) {
                    $query->withTrashed();
                },
                'customer' => function ($query) {
                    $query->withTrashed();
                },
                'supplier' => function ($query) {
                    $query->withTrashed();
                },
                'productInvoices' => function ($query) {
                    $query->with(['variant', 'product' => function ($query) {
                        $query->withTrashed()->with('productImage');
                    }]);
                }
            ])
            ->withTrashed()
            ->first();
    }

    public static function filterByTypeAndStatus($invoiceType = null, $payStatus = null)
    {
        $query = self::query();

        if (!is_null($invoiceType)) {
            $query->where('invoice_type', $invoiceType);
        }

        if (!is_null($payStatus)) {
            $query->where('pay_status', $payStatus);
        }

        return $query->get();
    }
}
