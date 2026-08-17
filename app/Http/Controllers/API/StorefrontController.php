<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\ImageModel;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * API công khai cho web bán hàng đọc catalogue.
 *
 * Chỉ đọc, chỉ trả về những gì cần hiển thị ngoài cửa hàng: không có giá nhập,
 * không có nhà cung cấp, không có thông tin nội bộ nào khác.
 */
class StorefrontController extends Controller
{
    /**
     * Danh sách sản phẩm còn bán, kèm biến thể và ảnh.
     */
    public function products(Request $request)
    {
        $products = Product::with([
            'category',
            'variants',
            'productInvoices.invoice',
            'productImage' => fn($q) => $q->orderBy('sort_order'),
        ])
            ->where('status', '!=', 0) // 0 = ngưng kinh doanh
            ->orderByDesc('is_featured')
            ->orderBy('product_name')
            ->get();

        return response()->json([
            'synced_at' => now()->toIso8601String(),
            'products' => $products->map(fn($p) => $this->transform($p))->values(),
            'categories' => $this->categories(),
        ]);
    }

    /**
     * Một sản phẩm theo slug - dùng để lấy tồn kho mới nhất ở trang chi tiết.
     */
    public function product(string $slug)
    {
        $product = Product::with([
            'category',
            'variants',
            'productInvoices.invoice',
            'productImage' => fn($q) => $q->orderBy('sort_order'),
        ])
            ->where('slug', $slug)
            ->where('status', '!=', 0)
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Không tìm thấy sản phẩm.'], 404);
        }

        return response()->json($this->transform($product));
    }

    /**
     * Cây danh mục đang dùng, kèm số sản phẩm.
     */
    public function categoriesIndex()
    {
        return response()->json(['categories' => $this->categories()]);
    }

    private function categories(): array
    {
        return Categories::where('status', 1)
            ->withCount(['products' => fn($q) => $q->where('status', '!=', 0)])
            ->orderBy('sort_order')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'parent_id' => $c->parent_id,
                'image' => $c->image ? $this->url($c->image) : null,
                'count' => $c->products_count,
            ])
            ->values()
            ->all();
    }

    private function transform(Product $product): array
    {
        $images = $product->productImage->where('media_type', ImageModel::TYPE_IMAGE);
        $videos = $product->productImage->where('media_type', ImageModel::TYPE_VIDEO);
        $pinned = $images->firstWhere('is_pined', true) ?? $images->first();

        // Ảnh ghim đứng đầu, phần còn lại giữ nguyên thứ tự sort_order.
        $gallery = $images
            ->sortByDesc(fn($i) => $pinned && $i->id === $pinned->id)
            ->map(fn($i) => $this->url($i->path))
            ->values();

        $price = (int) ($product->discount_price ?? $product->sell_price);

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->product_name,
            'category' => $product->category->name ?? 'Khác',
            'description' => $product->description,
            'material' => $product->material,
            'brand' => $product->brand,
            'audience' => $product->audience ?: 'Unisex',
            // Hàng mới về trong 30 ngày, để web gắn nhãn "mới" bằng dữ liệu thật.
            'is_new' => $product->created_at?->gt(now()->subDays(30)) ?? false,
            // Lượt bán thật: chỉ đếm đơn đã hoàn thành.
            'sold' => (int) $product->productInvoices
                ->filter(fn($line) => $line->invoice?->order_status === \App\Models\Invoice::STATUS_COMPLETED)
                ->sum('quantity'),
            'price' => $price,
            // Chỉ có giá gạch ngang khi thực sự đang giảm giá.
            'compare_price' => $product->discount_price && $product->discount_price < $product->sell_price
                ? (int) $product->sell_price
                : null,
            'is_featured' => (bool) $product->is_featured,
            'in_stock' => $product->variants->sum('quantity') > 0,
            'total_stock' => (int) $product->variants->sum('quantity'),
            'images' => $gallery->all(),
            'videos' => $videos->map(fn($v) => $this->url($v->path))->values()->all(),
            'variants' => $product->variants->map(fn($v) => [
                // Web bán hàng gửi id này lại khi đặt hàng, đừng đổi định dạng.
                'id' => $v->id,
                'size' => $v->size,
                'color' => $v->color,
                'sku' => $v->sku,
                'stock' => (int) $v->quantity,
                'price' => (int) ($v->price_override ?? $price),
            ])->values()->all(),
        ];
    }

    /**
     * Đường dẫn ảnh tính từ gốc site, ví dụ "/storage/images/abc.jpg".
     *
     * Cố ý KHÔNG dùng url() để ghép sẵn tên miền: url() lấy APP_URL, mà biến đó
     * hay bị để nguyên "http://localhost", khiến web bán hàng tải ảnh sai địa chỉ.
     * Bên web tự ghép với WAREHOUSE_API_URL - nó luôn biết mình gọi máy chủ nào.
     */
    private function url(string $path): string
    {
        return Storage::url($path);
    }
}
