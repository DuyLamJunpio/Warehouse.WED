<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                $q->where('sku', 'like', "%{$keyword}%")
                    ->orWhereHas('product', fn($p) => $p->where('product_name', 'like', "%{$keyword}%"));
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
            'low_stock' => (clone $base)->where('quantity', '>', 0)
                ->where('quantity', '<=', self::LOW_STOCK_THRESHOLD)->count(),
            'out_of_stock' => (clone $base)->where('quantity', '<=', 0)->count(),
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

        $variant = ProductVariant::with('product')->findOrFail($id);
        $before = (int) $variant->quantity;
        $after = (int) $data['quantity'];

        if ($before === $after) {
            return response()->json(['error' => 'Số lượng không thay đổi.'], 422);
        }

        DB::beginTransaction();
        try {
            $variant->quantity = $after;
            $variant->save();

            StockAdjustment::create([
                'variant_id' => $variant->id,
                'user_id' => $request->user()?->id,
                'quantity_before' => $before,
                'quantity_change' => $after - $before,
                'quantity_after' => $after,
                'reason' => $data['reason'],
                'note' => $data['note'] ?? null,
            ]);

            // Sản phẩm còn hàng hay không phụ thuộc tổng tồn của mọi biến thể.
            $product = $variant->product;
            $total = ProductVariant::where('product_id', $product->id)->sum('quantity');
            $product->status = $total > 0 ? 1 : 2;
            $product->save();

            DB::commit();

            return response()->json([
                'success' => 'Đã cập nhật tồn kho: ' . $before . ' → ' . $after . '.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Không cập nhật được tồn kho: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lịch sử điều chỉnh của một biến thể.
     */
    public function history(string $id)
    {
        $adjustments = StockAdjustment::with('user')
            ->where('variant_id', $id)
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn($a) => [
                'time' => $a->created_at->format('d/m/Y H:i'),
                'user' => $a->user->name ?? 'Không rõ',
                'before' => $a->quantity_before,
                'change' => $a->quantity_change,
                'after' => $a->quantity_after,
                'reason' => $a->reason_label,
                'note' => $a->note,
            ]);

        return response()->json($adjustments);
    }
}
