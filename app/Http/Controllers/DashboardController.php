<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Trang tổng quan.
 *
 * Mọi con số ở đây đều lấy từ dữ liệu thật. Doanh thu chỉ tính đơn đã HOÀN
 * THÀNH - đơn đang giao còn có thể bị hoàn, cộng vào doanh thu là tự lừa mình.
 */
class DashboardController extends Controller
{
    /** Số ngày vẽ trên biểu đồ doanh thu. */
    private const CHART_DAYS = 14;

    /** Đơn còn phải làm gì đó, dùng cho ô "cần xử lý". */
    private const OPEN_STATUSES = [
        Invoice::STATUS_PENDING,
        Invoice::STATUS_CONFIRMED,
        Invoice::STATUS_PACKING,
        Invoice::STATUS_SHIPPING,
    ];

    public function index()
    {
        return view('dashboard', [
            'kpi' => $this->kpi(),
            'chart' => $this->revenueSeries(),
            'topProducts' => $this->topProducts(),
            'lowStock' => $this->lowStock(),
            'recentOrders' => $this->recentOrders(),
            'statusCounts' => $this->statusCounts(),
        ]);
    }

    /**
     * Doanh thu của các đơn đã hoàn thành trong một khoảng thời gian.
     */
    private function revenueBetween(Carbon $from, Carbon $to): int
    {
        return (int) Invoice::orders()
            ->where('order_status', Invoice::STATUS_COMPLETED)
            ->whereBetween('created_at', [$from, $to])
            ->sum('total_amount');
    }

    private function kpi(): array
    {
        $today = $this->revenueBetween(now()->startOfDay(), now()->endOfDay());
        $yesterday = $this->revenueBetween(
            now()->subDay()->startOfDay(),
            now()->subDay()->endOfDay(),
        );

        return [
            'revenue_today' => $today,
            'revenue_yesterday' => $yesterday,
            // Không chia cho 0: hôm qua không bán được gì thì không có "phần trăm tăng".
            'revenue_change' => $yesterday > 0
                ? (int) round((($today - $yesterday) / $yesterday) * 100)
                : null,

            'revenue_month' => $this->revenueBetween(now()->startOfMonth(), now()->endOfMonth()),
            'revenue_last_month' => $this->revenueBetween(
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth(),
            ),

            'orders_open' => Invoice::orders()->whereIn('order_status', self::OPEN_STATUSES)->count(),
            'orders_pending' => Invoice::orders()->where('order_status', Invoice::STATUS_PENDING)->count(),
            'orders_month' => Invoice::orders()
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),

            // Giá vốn đang nằm trong kho, tính theo giá nhập.
            'stock_value' => (int) ProductVariant::join('products', 'products.id', '=', 'product_variants.product_id')
                ->whereNull('products.deleted_at')
                ->sum(DB::raw('product_variants.quantity * products.import_price')),
            'stock_units' => (int) ProductVariant::whereHas('product')->sum('quantity'),

            'products' => Product::count(),
            'customers' => Customer::count(),
            'customers_month' => Customer::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),

            'low_stock' => ProductVariant::whereHas('product')
                ->where('quantity', '>', 0)
                ->where('quantity', '<=', InventoryController::LOW_STOCK_THRESHOLD)
                ->count(),
            'out_of_stock' => ProductVariant::whereHas('product')->where('quantity', '<=', 0)->count(),
        ];
    }

    /**
     * Doanh thu từng ngày trong 14 ngày gần nhất.
     *
     * Ngày không có đơn nào vẫn phải có cột 0: nếu chỉ lấy các ngày có dữ liệu
     * thì biểu đồ co lại và nhìn như ngày nào cũng bán được hàng.
     */
    private function revenueSeries(): array
    {
        $from = now()->subDays(self::CHART_DAYS - 1)->startOfDay();

        $rows = Invoice::orders()
            ->where('order_status', Invoice::STATUS_COMPLETED)
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as ngay, SUM(total_amount) as tien')
            ->groupBy('ngay')
            ->pluck('tien', 'ngay');

        $days = [];
        for ($i = self::CHART_DAYS - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = [
                'label' => $date->format('d/m'),
                'value' => (int) ($rows[$date->toDateString()] ?? 0),
            ];
        }

        $values = array_column($days, 'value');
        $max = $values ? max($values) : 0;

        return [
            'days' => $days,
            'max' => $max,
            'total' => array_sum($values),
            'has_data' => $max > 0,
        ];
    }

    /**
     * Bán chạy nhất 30 ngày qua, tính theo số lượng đã bán của đơn hoàn thành.
     */
    private function topProducts(int $limit = 5): array
    {
        return DB::table('product_invoices')
            ->join('invoices', 'invoices.id', '=', 'product_invoices.invoice_id')
            ->join('products', 'products.id', '=', 'product_invoices.product_id')
            ->where('invoices.invoice_type', Invoice::TYPE_ORDER)
            ->where('invoices.order_status', Invoice::STATUS_COMPLETED)
            ->where('invoices.created_at', '>=', now()->subDays(30))
            ->whereNull('invoices.deleted_at')
            ->groupBy('products.id', 'products.product_name')
            ->select(
                'products.product_name',
                DB::raw('SUM(product_invoices.quantity) as da_ban'),
                DB::raw('SUM(product_invoices.quantity * product_invoices.unit_price) as doanh_thu'),
            )
            ->orderByDesc('da_ban')
            ->limit($limit)
            ->get()
            ->map(fn($r) => [
                'name' => $r->product_name,
                'sold' => (int) $r->da_ban,
                'revenue' => (int) $r->doanh_thu,
            ])
            ->all();
    }

    /**
     * Biến thể sắp hết hoặc đã hết, để biết cần nhập thêm gì.
     */
    private function lowStock(int $limit = 6): array
    {
        return ProductVariant::with('product')
            ->whereHas('product')
            ->where('quantity', '<=', InventoryController::LOW_STOCK_THRESHOLD)
            ->orderBy('quantity')
            ->limit($limit)
            ->get()
            ->map(fn($v) => [
                'product' => $v->product->product_name ?? '—',
                'label' => $v->label,
                'quantity' => (int) $v->quantity,
            ])
            ->all();
    }

    private function recentOrders(int $limit = 6): array
    {
        return Invoice::orders()
            ->with('customer')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'code' => $o->order_code ?? '#' . $o->id,
                'customer' => $o->shipping_name ?? ($o->customer->customer_name ?? 'Khách lẻ'),
                'total' => (int) $o->total_amount,
                'status' => $o->order_status,
                'status_label' => $o->order_status_label ?? 'Chưa có trạng thái',
                'time' => $o->created_at->diffForHumans(),
            ])
            ->all();
    }

    private function statusCounts(): array
    {
        $counts = Invoice::orders()
            ->select('order_status', DB::raw('count(*) as total'))
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        $out = [];
        foreach (Invoice::ORDER_STATUSES as $key => $label) {
            $out[] = ['key' => $key, 'label' => $label, 'count' => (int) ($counts[$key] ?? 0)];
        }

        return $out;
    }
}
