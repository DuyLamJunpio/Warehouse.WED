<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Quản lý đơn hàng bán (invoices có invoice_type = 1): xem danh sách, đổi trạng
 * thái, in phiếu và lập đơn ngay tại quầy bằng bảng "Tạo đơn" của trang này.
 *
 * Tồn kho được trừ ngay lúc lập đơn chứ không đợi xác nhận, để hai người bán
 * cùng lúc không bán trùng một món. Vì vậy khi đơn bị hủy hoặc khách hoàn hàng
 * thì phải cộng trả về kho.
 */
class OrderController extends Controller
{
    private const PER_PAGE = 15;

    /** Cách khách trả tiền khi mua trực tiếp tại quầy. */
    public const PAYMENT_METHODS = [
        'cash' => 'Tiền mặt',
        'banking' => 'Chuyển khoản',
        'card' => 'Quẹt thẻ',
    ];

    public function index(Request $request)
    {
        return view('order.index', $this->listData($request) + [
            'statuses' => Invoice::ORDER_STATUSES,
            'summary' => $this->summary(),
            'paymentMethods' => self::PAYMENT_METHODS,
        ]);
    }

    public function getData(Request $request)
    {
        return view('order.data', $this->listData($request));
    }

    private function listData(Request $request): array
    {
        // Mở trang Đơn hàng cũng là một dịp rà: nhân viên luôn thấy trạng thái đúng
        // mà không cần chạy cron nền.
        Invoice::cancelExpiredHolds();

        $keyword = trim((string) $request->input('keyword'));
        $status = $request->input('order_status');

        $query = Invoice::orders()
            ->with(['customer', 'user', 'productInvoices'])
            ->latest();

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('order_code', 'ilike', "%{$keyword}%")
                    ->orWhere('shipping_name', 'ilike', "%{$keyword}%")
                    ->orWhere('shipping_phone', 'ilike', "%{$keyword}%")
                    ->orWhereHas('customer', fn($c) => $c->where('customer_name', 'ilike', "%{$keyword}%"));
            });
        }

        if (!empty($status)) {
            $query->where('order_status', $status);
        }

        return ['orders' => $query->paginate(self::PER_PAGE)->withQueryString()];
    }

    /**
     * Đếm đơn theo từng trạng thái + doanh thu đã ghi nhận.
     */
    private function summary(): array
    {
        $counts = Invoice::orders()
            ->select('order_status', DB::raw('count(*) as total'))
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        return [
            'by_status' => $counts,
            'total_orders' => (int) $counts->sum(),
            /**
             * Doanh thu = tiền đã thực sự nằm trong tay shop.
             *
             * Đơn chuyển khoản đã nhận được tiền (pay_status = 1) tính ngay, không
             * phải đợi giao xong: tiền đã về tài khoản rồi, treo nó lại nửa tháng
             * chỉ khiến con số trên màn hình không khớp với sao kê ngân hàng. Đơn
             * bán tại quầy cũng nằm nhóm này vì khách trả ngay.
             *
             * Đơn hoàn hàng và đơn huỷ bị trừ ra kể cả khi đã trả tiền — hàng quay
             * về kho thì khoản đó không còn là doanh thu.
             */
            'revenue' => (int) Invoice::orders()
                ->whereNotIn('order_status', [Invoice::STATUS_CANCELLED, Invoice::STATUS_RETURNED])
                ->where(fn($q) => $q->where('order_status', Invoice::STATUS_COMPLETED)
                    ->orWhere('pay_status', 1))
                ->sum('total_amount'),
        ];
    }

    public function show(string $id)
    {
        $order = Invoice::orders()
            ->with(['customer', 'user', 'productInvoices.product', 'productInvoices.variant'])
            ->findOrFail($id);

        return response()->json([
            'id' => $order->id,
            'order_code' => $order->order_code,
            'order_status' => $order->order_status,
            'status_label' => $order->order_status_label,
            'next_statuses' => collect($order->next_statuses)
                ->map(fn($s) => ['value' => $s, 'label' => Invoice::ORDER_STATUSES[$s]])
                ->values(),
            'created_at' => $order->created_at->format('d/m/Y H:i'),
            'customer' => $order->customer->customer_name ?? null,
            'shipping_name' => $order->shipping_name,
            'shipping_phone' => $order->shipping_phone,
            'shipping_address' => $order->shipping_address,
            'shipping_fee' => (int) $order->shipping_fee,
            'payment_method' => $order->payment_method,
            'pay_status' => $order->pay_status,
            'note' => $order->note,
            'subtotal' => $order->subtotal,
            'discount' => (int) $order->discount,
            'total_amount' => (int) $order->total_amount,
            'seller' => $order->user->name ?? null,
            'items' => $order->productInvoices->map(fn($line) => [
                'product' => $line->product->product_name ?? 'Sản phẩm đã xóa',
                'variant' => $line->variant->label ?? '—',
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
                'line_total' => $line->line_total,
            ]),
        ]);
    }

    /**
     * Đổi trạng thái đơn theo luồng đã định, cộng trả hàng về kho khi hủy/hoàn.
     */
    public function updateStatus(Request $request, string $id)
    {
        $data = $request->validate([
            'order_status' => ['required', Rule::in(array_keys(Invoice::ORDER_STATUSES))],
        ]);

        $order = Invoice::orders()->with('productInvoices')->findOrFail($id);
        $newStatus = $data['order_status'];

        if (!$order->canTransitionTo($newStatus)) {
            return response()->json([
                'error' => 'Không thể chuyển từ "' . $order->order_status_label
                    . '" sang "' . Invoice::ORDER_STATUSES[$newStatus] . '".',
            ], 422);
        }

        DB::beginTransaction();
        try {
            if (in_array($newStatus, Invoice::STATUS_RESTOCK, true)) {
                $order->restockLines();
            }

            $order->order_status = $newStatus;
            $order->save();

            DB::commit();

            return response()->json([
                'success' => 'Đã chuyển đơn sang "' . Invoice::ORDER_STATUSES[$newStatus] . '".',
                // Gửi kèm số liệu mới: đổi trạng thái là đổi luôn cả doanh thu lẫn
                // các ô đếm, để màn hình khỏi hiện số cũ cho tới lần tải trang sau.
                'summary' => $this->summary(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Đổi trạng thái đơn hàng thất bại: ' . $e->getMessage());
            return response()->json(['error' => 'Không đổi được trạng thái: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Phiếu giao hàng để in.
     */
    public function print(string $id)
    {
        $order = Invoice::orders()
            ->with(['customer', 'user', 'productInvoices.product', 'productInvoices.variant'])
            ->findOrFail($id);

        return view('order.print', compact('order'));
    }

    /**
     * Tìm biến thể còn hàng theo tên sản phẩm hoặc SKU, để nhân viên chọn nhanh
     * trong bảng tạo đơn tại quầy.
     */
    public function searchVariants(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));

        $query = ProductVariant::with('product')
            ->whereHas('product', fn($q) => $q->where('status', '!=', 0))
            ->where('quantity', '>', 0);

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('sku', 'ilike', "%{$keyword}%")
                    ->orWhereHas('product', fn($p) => $p->where('product_name', 'ilike', "%{$keyword}%"));
            });
        }

        $variants = $query->orderByDesc('quantity')->limit(30)->get();

        return response()->json($variants->map(fn($v) => [
            'id' => $v->id,
            'product' => $v->product->product_name,
            'label' => $v->label,
            'sku' => $v->sku,
            'stock' => (int) $v->quantity,
            'price' => (int) ($v->price_override ?? $v->product->discount_price ?? $v->product->sell_price),
        ])->values());
    }

    /**
     * Lập đơn cho khách mua trực tiếp tại quầy.
     *
     * Khác đơn đặt trên web: khách cầm hàng về ngay và trả tiền ngay, nên đơn được
     * lập thẳng ở trạng thái "Hoàn thành" và "Đã thanh toán", không có địa chỉ giao
     * và không đi qua các bước đóng gói / vận chuyển. Muốn trả hàng thì chuyển đơn
     * sang "Hoàn hàng", lúc đó tồn kho tự được cộng trả lại.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',

            // Khách vãng lai thì bỏ trống hết, đơn ghi là "Khách lẻ".
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',

            'payment_method' => ['required', Rule::in(array_keys(self::PAYMENT_METHODS))],
            'discount' => 'nullable|integer|min:0',
            'note' => 'nullable|string|max:1000',
        ]);

        // Gộp dòng trùng biến thể để không trừ kho hai lần cho cùng một món.
        $wanted = [];
        foreach ($data['items'] as $item) {
            $id = (int) $item['variant_id'];
            $wanted[$id] = ($wanted[$id] ?? 0) + (int) $item['quantity'];
        }

        DB::beginTransaction();
        try {
            // Khoá dòng biến thể: quầy và web có thể bán trùng một món cùng lúc.
            $variants = ProductVariant::with('product')
                ->whereIn('id', array_keys($wanted))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = [];
            $subtotal = 0;

            foreach ($wanted as $variantId => $quantity) {
                $variant = $variants->get($variantId);

                if (!$variant || !$variant->product) {
                    throw new \RuntimeException('Sản phẩm không còn tồn tại.');
                }

                // Hàng không theo dõi tồn kho vẫn bán được dù kho ghi 0.
                if ($variant->product->manage_stock && $variant->quantity < $quantity) {
                    throw new \RuntimeException(
                        $variant->product->product_name . ' (' . $variant->label
                        . ') chỉ còn ' . $variant->quantity . ' sản phẩm.'
                    );
                }

                // Giá lấy từ CSDL, không nhận giá gửi lên từ trình duyệt.
                $unitPrice = (int) ($variant->price_override
                    ?? $variant->product->discount_price
                    ?? $variant->product->sell_price);

                $lines[] = ['variant' => $variant, 'quantity' => $quantity, 'unit_price' => $unitPrice];
                $subtotal += $unitPrice * $quantity;
            }

            $discount = min((int) ($data['discount'] ?? 0), $subtotal);

            // Có số điện thoại thì gộp vào hồ sơ khách để còn thống kê khách quen.
            $customer = null;
            if (!empty($data['customer_phone'])) {
                $customer = Customer::mergeByPhone([
                    'customer_name' => $data['customer_name'] ?: 'Khách lẻ',
                    'customer_phone' => $data['customer_phone'],
                ]);
            }

            $invoice = Invoice::create([
                'invoice_type' => Invoice::TYPE_ORDER,
                'order_code' => $this->generateOrderCode(),
                // Khách cầm hàng về ngay: đơn xong luôn, không qua đóng gói/giao hàng.
                'order_status' => Invoice::STATUS_COMPLETED,
                'pay_status' => 1,
                'customer_id' => $customer?->id,
                'user_id' => $request->user()->id,
                'total_amount' => $subtotal - $discount,
                'discount' => $discount,
                'shipping_fee' => 0,
                'payment_method' => $data['payment_method'],
                'note' => $data['note'] ?? null,
                'signature_name' => $request->user()->name,
            ]);

            foreach ($lines as $line) {
                DB::table('product_invoices')->insert([
                    'invoice_id' => $invoice->id,
                    'product_id' => $line['variant']->product_id,
                    'variant_id' => $line['variant']->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // max(0) để hàng không theo dõi tồn kho không tụt xuống số âm.
                $line['variant']->quantity = max(0, $line['variant']->quantity - $line['quantity']);
                $line['variant']->save();
            }

            foreach (array_unique(array_map(fn($l) => $l['variant']->product_id, $lines)) as $productId) {
                $total = ProductVariant::where('product_id', $productId)->sum('quantity');
                Product::where('id', $productId)->update(['status' => $total > 0 ? 1 : 2]);
            }

            DB::commit();

            return response()->json([
                'success' => 'Đã lập đơn ' . $invoice->order_code . '.',
                'order_code' => $invoice->order_code,
                'total_amount' => $invoice->total_amount,
                'print_url' => '/order/' . $invoice->id . '/print',
            ]);
        } catch (\RuntimeException $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lập đơn tại quầy thất bại: ' . $e->getMessage());
            return response()->json(['error' => 'Không lập được đơn: ' . $e->getMessage()], 500);
        }
    }

    private function generateOrderCode(): string
    {
        do {
            // Tiền tố khác đơn web để nhìn mã là biết đơn bán tại quầy.
            $code = 'BQ' . now()->format('ymd') . strtoupper(Str::random(4));
        } while (Invoice::withTrashed()->where('order_code', $code)->exists());

        return $code;
    }
}
