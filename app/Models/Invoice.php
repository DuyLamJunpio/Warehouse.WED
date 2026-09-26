<?php

namespace App\Models;

use App\Services\StockAllocator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        'legacy_order_code',
        'checkout_ref',
        'checkout_fingerprint',
        'order_status',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'shipping_fee',
        'shop_shipping_fee',
        'payment_method',
        'payment_expires_at',
        'stock_deducted_at',
        // Tiền in của cả đơn, cộng dồn từ mọi mẫu. Tách riêng khỏi tiền hàng để
        // lúc tính lãi còn phân biệt được hai khoản.
        'print_fee',
        // Tài khoản nhận hoàn tiền, khách để lại lúc đặt đơn in.
        'refund_bank_name',
        'refund_account_number',
        'refund_account_name',
    ];

    protected $casts = [
        'payment_expires_at' => 'datetime',
        'stock_deducted_at' => 'datetime',
        'print_fee' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Các mẫu áo khách đã thiết kế trong đơn này.
     *
     * Nhiều chứ không phải một: mỗi mẫu là một món trong giỏ, nên đơn áo lớp có
     * thể gồm cùng một hình trên ba size khác nhau, mỗi size một mẫu.
     */
    public function printDesigns()
    {
        return $this->hasMany(PrintDesign::class, 'invoice_id');
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

    /** Các dòng sổ cái tồn kho phát sinh từ đơn này. */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
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
    /** Trừ tồn đúng một lần khi đơn được xác nhận hoặc hoàn thành tại quầy. */
    public function deductStockLines(): void
    {
        if ($this->stock_deducted_at !== null) {
            return;
        }

        $wanted = [];
        foreach ($this->productInvoices()->get() as $line) {
            if (! $line->variant_id) {
                throw new \RuntimeException("Dòng hàng #{$line->id} chưa có biến thể để trừ tồn.");
            }

            $wanted[$line->variant_id] = ($wanted[$line->variant_id] ?? 0) + (int) $line->quantity;
        }

        /** @var StockAllocator $allocator */
        $allocator = app(StockAllocator::class);
        $requested = $allocator->requirements($wanted)['requirements'];

        // Phôi in cũng là hàng thật: cộng vào cùng nhu cầu trước khi kiểm tồn.
        $printDesigns = $this->printDesigns()->with('blank')->get();
        foreach ($printDesigns as $printDesign) {
            if (! $printDesign->blank?->product_id) {
                continue;
            }

            $blankVariant = ProductVariant::where('product_id', $printDesign->blank->product_id)
                ->where('size', $printDesign->size)
                ->where('color', $printDesign->color_name)
                ->lockForUpdate()
                ->first();

            if (! $blankVariant) {
                Log::warning(sprintf(
                    'Đơn in %s: không tìm thấy biến thể %s/%s của sản phẩm #%d để trừ tồn.',
                    $printDesign->code,
                    $printDesign->size,
                    $printDesign->color_name,
                    $printDesign->blank->product_id,
                ));
                continue;
            }

            $requested[$blankVariant->id] = ($requested[$blankVariant->id] ?? 0) + $printDesign->qty;
        }

        if ($requested === []) {
            $this->stock_deducted_at = now();
            return;
        }

        $variants = ProductVariant::with('product')
            ->whereIn('id', array_keys($requested))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $allocator->assertSufficient($requested, $variants);

        $touchedProducts = [];
        foreach ($requested as $variantId => $quantity) {
            $variant = $variants->get($variantId);
            if (! $variant->product->manage_stock) {
                continue;
            }

            $before = (int) $variant->quantity;
            $variant->quantity -= $quantity;
            $variant->save();
            $touchedProducts[$variant->product_id] = true;

            StockMovement::create([
                'variant_id' => $variant->id,
                'invoice_id' => $this->id,
                'user_id' => $this->user_id,
                'type' => StockMovement::TYPE_SALE,
                'quantity_before' => $before,
                'quantity_change' => -$quantity,
                'quantity_after' => (int) $variant->quantity,
                'note' => 'Xuất kho theo đơn ' . ($this->order_code ?: '#' . $this->id),
            ]);
        }

        foreach (array_keys($touchedProducts) as $productId) {
            $product = Product::find($productId);
            if ($product?->is_combo) {
                $allocator->syncComboProductStatus($product);
                continue;
            }
            $total = ProductVariant::where('product_id', $productId)->sum('quantity');
            Product::where('id', $productId)->update(['status' => $total > 0 ? 1 : 2]);
        }
        $allocator->syncAffectedComboStatuses(array_keys($requested));

        $this->stock_deducted_at = now();
    }

    /**
     * Cộng trả số lượng của từng dòng hàng về đúng biến thể đã bán.
     * Chỉ hoàn khi đơn đã từng trừ tồn.
     */
    public function restockLines(): void
    {
        if ($this->stock_deducted_at === null) {
            return;
        }

        /** @var StockAllocator $allocator */
        $allocator = app(StockAllocator::class);
        // Dùng đúng các dòng xuất kho đã ghi lúc xác nhận, đặc biệt quan trọng
        // khi công thức combo đã được chỉnh sau đó. Đơn cũ chưa có sổ cái mới
        // mới rơi về cách tính từ dòng hóa đơn như trước.
        $requested = StockMovement::where('invoice_id', $this->id)
            ->where('type', StockMovement::TYPE_SALE)
            ->selectRaw('variant_id, SUM(ABS(quantity_change)) AS quantity')
            ->groupBy('variant_id')
            ->pluck('quantity', 'variant_id')
            ->mapWithKeys(fn ($quantity, $variantId) => [(int) $variantId => (int) $quantity])
            ->all();

        if ($requested === []) {
            $wanted = [];
            foreach ($this->productInvoices()->get() as $line) {
                if (! $line->variant_id) {
                    Log::warning("Dòng hàng #{$line->id} của đơn #{$this->id} không có variant_id, bỏ qua khi hoàn kho.");
                    continue;
                }
                $wanted[$line->variant_id] = ($wanted[$line->variant_id] ?? 0) + (int) $line->quantity;
            }
            $requested = $allocator->requirements($wanted)['requirements'];
        }
        if ($requested === []) {
            return;
        }

        $variants = ProductVariant::with('product')
            ->whereIn('id', array_keys($requested))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $touchedProducts = [];
        foreach ($requested as $variantId => $quantity) {
            $variant = $variants->get($variantId);
            if (! $variant) {
                throw new \RuntimeException("Không tìm thấy biến thể #{$variantId} để hoàn kho.");
            }
            if (! $variant->product?->manage_stock) {
                continue;
            }

            $before = (int) $variant->quantity;
            $variant->quantity += $quantity;
            $variant->save();
            $touchedProducts[$variant->product_id] = true;

            StockMovement::create([
                'variant_id' => $variant->id,
                'invoice_id' => $this->id,
                'user_id' => $this->user_id,
                'type' => StockMovement::TYPE_RESTOCK,
                'quantity_before' => $before,
                'quantity_change' => $quantity,
                'quantity_after' => (int) $variant->quantity,
                'note' => 'Hoàn kho từ đơn ' . ($this->order_code ?: '#' . $this->id),
            ]);
        }

        foreach (array_keys($touchedProducts) as $productId) {
            $product = Product::find($productId);
            if ($product?->is_combo) {
                $allocator->syncComboProductStatus($product);
                continue;
            }
            $total = ProductVariant::where('product_id', $productId)->sum('quantity');
            Product::where('id', $productId)->update(['status' => $total > 0 ? 1 : 2]);
        }
        $allocator->syncAffectedComboStatuses(array_keys($requested));
    }
    /**
     * Huỷ những đơn web quá hạn thanh toán. Chỉ hoàn kho nếu đơn đã từng trừ tồn.
     *
     * Cố ý KHÔNG chạy bằng cron mà gọi ngay tại những chỗ hàng thật sự được cần:
     * lúc khách khác kiểm tồn hoặc đặt hàng, và lúc nhân viên mở trang Đơn hàng.
     */
    public static function cancelExpiredHolds(): int
    {
        $grace = (int) config('services.storefront.expiry_grace_minutes', 5);

        $ids = self::orders()
            ->where('order_status', self::STATUS_PENDING)
            ->where('pay_status', 0)
            ->whereNotNull('payment_expires_at')
            ->where('payment_expires_at', '<', now()->subMinutes($grace))
            ->pluck('id');

        if ($ids->isEmpty()) {
            return 0;
        }

        $cancelled = 0;

        foreach ($ids as $id) {
            DB::beginTransaction();
            try {
                // Khoá dòng rồi kiểm tra lại: webhook báo đã thanh toán có thể chạy
                // xen vào giữa lúc đọc và lúc ghi, không được huỷ nhầm đơn vừa trả tiền.
                $order = self::with('productInvoices')->lockForUpdate()->find($id);

                if (
                    !$order
                    || $order->order_status !== self::STATUS_PENDING
                    || (int) $order->pay_status === 1
                ) {
                    DB::rollBack();
                    continue;
                }

                $order->restockLines();
                $order->order_status = self::STATUS_CANCELLED;
                $order->note = trim(($order->note ? $order->note . "\n" : '')
                    . 'Tự huỷ: quá hạn thanh toán lúc ' . $order->payment_expires_at . '.');
                $order->save();

                DB::commit();
                $cancelled++;
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Huỷ đơn quá hạn #{$id} thất bại: " . $e->getMessage());
            }
        }

        return $cancelled;
    }
}
