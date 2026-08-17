<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Quản lý đơn hàng bán (invoices có invoice_type = 1).
 *
 * Tồn kho được trừ ngay lúc lập đơn (ở InvoiceController@store) chứ không đợi
 * xác nhận, để hai người bán cùng lúc không bán trùng một món. Vì vậy khi đơn
 * bị hủy hoặc khách hoàn hàng thì phải cộng trả về kho.
 */
class OrderController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request)
    {
        return view('order.index', $this->listData($request) + [
            'statuses' => Invoice::ORDER_STATUSES,
            'summary' => $this->summary(),
        ]);
    }

    public function getData(Request $request)
    {
        return view('order.data', $this->listData($request));
    }

    private function listData(Request $request): array
    {
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
     * Đếm đơn theo từng trạng thái + doanh thu từ đơn đã hoàn thành.
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
            // Chỉ tính đơn đã hoàn thành: đơn đang giao có thể bị hoàn.
            'revenue' => (int) Invoice::orders()
                ->where('order_status', Invoice::STATUS_COMPLETED)
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
                $this->restock($order);
            }

            $order->order_status = $newStatus;
            $order->save();

            DB::commit();

            return response()->json([
                'success' => 'Đã chuyển đơn sang "' . Invoice::ORDER_STATUSES[$newStatus] . '".',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Đổi trạng thái đơn hàng thất bại: ' . $e->getMessage());
            return response()->json(['error' => 'Không đổi được trạng thái: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cộng trả số lượng của từng dòng hàng về đúng biến thể đã bán.
     */
    private function restock(Invoice $order): void
    {
        $touchedProducts = [];

        foreach ($order->productInvoices as $line) {
            if (!$line->variant_id) {
                // Đơn cũ chưa gắn biến thể thì không biết trả về size/màu nào,
                // bỏ qua để không cộng nhầm kho.
                Log::warning("Dòng hàng #{$line->id} của đơn #{$order->id} không có variant_id, bỏ qua khi hoàn kho.");
                continue;
            }

            $variant = ProductVariant::find($line->variant_id);
            if (!$variant) {
                continue;
            }

            $variant->quantity += $line->quantity;
            $variant->save();

            $touchedProducts[$variant->product_id] = true;
        }

        foreach (array_keys($touchedProducts) as $productId) {
            $total = ProductVariant::where('product_id', $productId)->sum('quantity');
            Product::where('id', $productId)->update(['status' => $total > 0 ? 1 : 2]);
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
}
