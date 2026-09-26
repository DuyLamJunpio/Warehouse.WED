<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Invoice;
use App\Models\ProductInvoice;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Services\StockAllocator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Quản lý tồn kho theo từng biến thể size/màu.
 */
class InventoryController extends Controller
{
    /** Dưới ngưỡng này thì coi là sắp hết hàng. */
    public const LOW_STOCK_THRESHOLD = 5;

    private const PER_PAGE = 20;

    public function index(Request $request)
    {
        return view('inventory.index', $this->listData($request) + [
            'categories' => Categories::where('status', 1)->orderBy('name')->get(),
            'reasons' => StockAdjustment::REASONS,
            'summary' => $this->summary(),
        ]);
    }

    public function getData(Request $request)
    {
        return view('inventory.data', $this->listData($request));
    }

    /**
     * Danh sách biến thể kèm bộ lọc từ khóa / danh mục / tình trạng tồn.
     */
    private function listData(Request $request): array
    {
        $keyword = trim((string) $request->input('keyword'));
        $categoryId = $request->input('categories_id');
        $stockFilter = $request->input('stock'); // low | out | null

        $query = ProductVariant::with(['product.category'])
            ->whereHas('product') // bỏ biến thể của sản phẩm đã xóa mềm
            ->orderBy('quantity');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('sku', 'ilike', "%{$keyword}%")
                    ->orWhereHas('product', fn($p) => $p->where('product_name', 'ilike', "%{$keyword}%"));
            });
        }

        if (!empty($categoryId)) {
            $query->whereHas('product', fn($p) => $p->where('categories_id', $categoryId));
        }

        if ($stockFilter === 'out') {
            $query->where('quantity', '<=', 0);
        } elseif ($stockFilter === 'low') {
            // Sắp hết = còn hàng nhưng dưới ngưỡng. Hết sạch đã có bộ lọc riêng.
            $query->where('quantity', '>', 0)->where('quantity', '<=', self::LOW_STOCK_THRESHOLD);
        }

        return [
            'variants' => $query->paginate(self::PER_PAGE)->withQueryString(),
            'threshold' => self::LOW_STOCK_THRESHOLD,
        ];
    }

    /**
     * Số liệu tổng quan hiển thị ở đầu trang.
     */
    private function summary(): array
    {
        $base = ProductVariant::whereHas('product');

        return [
            'total_quantity' => (int) (clone $base)->sum('quantity'),
            'total_variants' => (clone $base)->count(),
            // Cảnh báo chỉ tính hàng có theo dõi tồn kho; hàng đặt may nằm im ở
            // mức 0 nên gộp vào là hai con số này mất hết ý nghĩa.
            'low_stock' => ProductVariant::whereHas('product', fn($q) => $q->where('manage_stock', true))
                ->where('quantity', '>', 0)
                ->where('quantity', '<=', self::LOW_STOCK_THRESHOLD)->count(),
            'out_of_stock' => ProductVariant::whereHas('product', fn($q) => $q->where('manage_stock', true))
                ->where('quantity', '<=', 0)->count(),
            // Giá vốn đang nằm trong kho, tính theo giá nhập của sản phẩm.
            'stock_value' => (int) ProductVariant::whereHas('product')
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->sum(DB::raw('product_variants.quantity * products.import_price')),
        ];
    }

    /**
     * Điều chỉnh tồn kho thủ công và ghi lại nhật ký.
     */
    public function adjust(Request $request, string $id)
    {
        $data = $request->validate([
            // Số lượng mới sau điều chỉnh, không phải mức chênh lệch.
            'quantity' => 'required|integer|min:0',
            'reason' => ['required', Rule::in(array_keys(StockAdjustment::REASONS))],
            'note' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $variant = ProductVariant::with(['product', 'comboComponents'])
                ->lockForUpdate()->findOrFail($id);
            if ($variant->comboComponents->isNotEmpty()) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Combo có tồn được tính từ thành phần. Hãy điều chỉnh tồn của từng thành phần thay vì sửa trực tiếp combo.',
                ], 422);
            }

            $before = (int) $variant->quantity;
            $after = (int) $data['quantity'];
            if ($before === $after) {
                DB::rollBack();
                return response()->json(['error' => 'Số lượng không thay đổi.'], 422);
            }

            $variant->quantity = $after;
            $variant->save();

            StockMovement::create([
                'variant_id' => $variant->id,
                'user_id' => $request->user()?->id,
                'type' => StockMovement::TYPE_MANUAL,
                'quantity_before' => $before,
                'quantity_change' => $after - $before,
                'quantity_after' => $after,
                'reason' => $data['reason'],
                'note' => $data['note'] ?? null,
            ]);

            // Sản phẩm còn hàng hay không phụ thuộc tổng tồn của mọi biến thể.
            $product = $variant->product;
            $allocator = app(StockAllocator::class);
            if ($product->is_combo) {
                $allocator->syncComboProductStatus($product);
            } else {
                $total = ProductVariant::where('product_id', $product->id)->sum('quantity');
                $product->status = $total > 0 ? 1 : 2;
                $product->save();
            }
            $allocator->syncAffectedComboStatuses([$variant->id]);

            DB::commit();

            return response()->json([
                'success' => 'Đã cập nhật tồn kho: ' . $before . ' → ' . $after . '.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Điều chỉnh tồn kho thất bại.', ['exception' => $e]);

            return response()->json([
                'error' => 'Chưa thể cập nhật tồn kho. Số lượng hiện tại chưa thay đổi; vui lòng tải lại trang rồi thử lại.',
            ], 500);
        }
    }

    /**
     * Lịch sử điều chỉnh của một biến thể.
     */
    public function history(string $id)
    {
        $variant = ProductVariant::findOrFail($id);
        $movements = StockMovement::with(['user', 'invoice'])
            ->where('variant_id', $id)
            ->latest()
            ->limit(50)
            ->get()
            ->map(function (StockMovement $movement) {
                $reason = $movement->type_label;
                if ($movement->type === StockMovement::TYPE_MANUAL && $movement->reason) {
                    $reason .= ': ' . (StockAdjustment::REASONS[$movement->reason] ?? $movement->reason);
                }
                if ($movement->invoice?->order_code) {
                    $reason .= ' · ' . $movement->invoice->order_code;
                }

                return [
                    'time' => $movement->created_at,
                    'user' => $movement->user->name ?? 'Hệ thống',
                    'before' => $movement->quantity_before,
                    'change' => $movement->quantity_change,
                    'after' => $movement->quantity_after,
                    'reason' => $reason,
                    'note' => $movement->note,
                ];
            });

        // Các đơn được tạo trước khi có sổ cái đã trừ thẳng vào quantity. Hiển
        // thị chúng như biến động chỉ đọc để lịch sử không bị đứt đoạn sau khi
        // triển khai, nhưng tuyệt đối không ghi lại lần nữa vào tồn kho.
        $legacyLines = ProductInvoice::with(['invoice.user'])
            ->where('variant_id', $id)
            ->whereHas('invoice', fn ($query) => $query
                ->where('invoice_type', Invoice::TYPE_ORDER)
                ->whereNotNull('stock_deducted_at'))
            ->orderBy('created_at')
            ->get();

        $legacyInvoiceIds = $legacyLines->pluck('invoice_id')->unique()->values();
        $invoicesWithLedger = $legacyInvoiceIds->isEmpty()
            ? collect()
            : StockMovement::whereIn('invoice_id', $legacyInvoiceIds)
                ->pluck('invoice_id')
                ->unique();

        $legacySales = $legacyLines
            ->reject(fn (ProductInvoice $line) => $invoicesWithLedger->contains($line->invoice_id))
            ->flatMap(function (ProductInvoice $line) {
                $invoice = $line->invoice;
                if (! $invoice) {
                    return [];
                }

                $rows = [[
                    'time' => $invoice->stock_deducted_at ?? $line->created_at,
                    'user' => $invoice->user->name ?? 'Hệ thống',
                    'before' => null,
                    'change' => -(int) $line->quantity,
                    'after' => null,
                    'reason' => 'Bán hàng · ' . ($invoice->order_code ?: '#' . $invoice->id),
                    'note' => 'Đơn cũ chưa có sổ cái tồn kho; số trước/sau được suy ra từ tồn hiện tại.',
                ]];

                if (in_array($invoice->order_status, Invoice::STATUS_RESTOCK, true)) {
                    $rows[] = [
                        'time' => $invoice->updated_at ?? $line->created_at,
                        'user' => $invoice->user->name ?? 'Hệ thống',
                        'before' => null,
                        'change' => (int) $line->quantity,
                        'after' => null,
                        'reason' => 'Hoàn kho · ' . ($invoice->order_code ?: '#' . $invoice->id),
                        'note' => 'Đơn cũ chưa có sổ cái tồn kho; số trước/sau được suy ra từ tồn hiện tại.',
                    ];
                }

                return $rows;
            });

        // Giữ lịch sử kiểm kê đã có trước khi chuyển sang sổ cái stock_movements.
        $legacyAdjustments = StockAdjustment::with('user')
            ->where('variant_id', $id)
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (StockAdjustment $a) => [
                'time' => $a->created_at,
                'user' => $a->user->name ?? 'Không rõ',
                'before' => $a->quantity_before,
                'change' => $a->quantity_change,
                'after' => $a->quantity_after,
                'reason' => $a->reason_label,
                'note' => $a->note,
            ]);

        $rows = $movements->concat($legacyAdjustments)->concat($legacySales)
            ->sortByDesc('time')
            ->take(50)
            ->values();

        // Các dòng legacy không có before/after. Dựng ngược từ tồn hiện tại và
        // các biến động đã biết để người dùng vẫn thấy được số lượng đã bán.
        $runningAfter = (int) $variant->quantity;
        $rows = $rows->map(function (array $row) use (&$runningAfter) {
            if ($row['before'] === null || $row['after'] === null) {
                $row['after'] = $runningAfter;
                $row['before'] = $runningAfter - (int) $row['change'];
            }
            $runningAfter = (int) $row['before'];

            return array_merge($row, ['time' => $row['time']->format('d/m/Y H:i')]);
        });

        return response()->json($rows);
    }
}
