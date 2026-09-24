<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Categories;
use App\Models\Collection;
use App\Models\ImageModel;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SiteText;
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
            'variants.style',
            'styles.image',
            'productInvoices.invoice',
            'productImage' => fn($q) => $q->orderBy('sort_order'),
        ])
            ->storefrontVisible()
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
     * Một sản phẩm theo slug hoặc ID dùng trong đường dẫn /san-pham/{id}.
     * Trang chi tiết đọc riêng endpoint này để lấy giá và tồn kho mới nhất.
     */
    public function product(string $slug)
    {
        $product = Product::with([
            'category',
            'variants.style',
            'styles.image',
            'productInvoices.invoice',
            'productImage' => fn($q) => $q->orderBy('sort_order'),
        ])
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug);
                if (ctype_digit($slug)) {
                    $query->orWhere('id', (int) $slug);
                }
            })
            ->storefrontVisible()
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

    /**
     * Nội dung trang chủ chỉnh từ trang quản trị: slide hero, bộ sưu tập và tiêu đề.
     *
     * Chỉ trả về những mục đang trong thời gian hiển thị. Tiêu đề chưa ai sửa
     * thì không có trong danh sách, web tự dùng chữ mặc định của nó.
     */
    public function content()
    {
        $banners = Banner::live()->get()->map(fn($b) => [
            'id' => $b->id,
            'media' => $this->url($b->media_path),
            'media_type' => $b->media_type,
            'poster' => $b->poster_path ? $this->url($b->poster_path) : null,
            'mobile' => $b->mobile_path ? $this->url($b->mobile_path) : null,
            'alt' => $b->alt,
            'heading' => $b->heading,
            'subheading' => $b->subheading,
            'cta_label' => $b->cta_label,
            'cta_link' => $b->cta_link,
        ])->values();

        // Mỗi bộ sưu tập đang hiện được dựng thành một khối riêng trên trang chủ.
        $collections = Collection::live()
            ->with(['products' => fn($q) => $q->storefrontVisible()])
            ->get()
            ->map(fn($collection) => [
                'id' => $collection->id,
                'title' => $collection->title,
                'subtitle' => $collection->subtitle,
                'image' => $collection->image_path ? $this->url($collection->image_path) : null,
                'cta_label' => $collection->cta_label,
                'cta_link' => $collection->cta_link,
                'product_slugs' => $collection->products->pluck('slug')->values(),
            ])
            ->values();

        return response()->json([
            'banners' => $banners,
            // Giữ khoá đơn để các bản web bán hàng cũ vẫn đọc được bộ đầu tiên.
            'collection' => $collections->first(),
            'collections' => $collections,
            // Marquee giữa trang chủ đã ngừng sử dụng. Giữ lại khoá với mảng
            // rỗng để các phiên bản web bán hàng cũ không bị lỗi khi đọc API.
            'marquee' => [],
            'announcement' => SiteText::announcement()->live()->pluck('value')->values(),
            'headings' => SiteText::heading()->live()->pluck('value', 'key'),
            // Hình thức thanh toán và phí giao hàng do trang quản trị quyết định.
            // Web bán hàng chỉ hiển thị theo, còn số tiền thật vẫn do máy chủ tính
            // lại lúc đặt hàng - không tin con số đi qua trình duyệt.
            'sales' => $this->sales(),
        ]);
    }

    /**
     * Cài đặt bán hàng dưới dạng web bán hàng dùng được ngay.
     *
     * Chỉ trả phí mà KHÁCH phải trả: phần shop tự gánh là chuyện nội bộ, đưa ra
     * ngoài chỉ khiến trang thanh toán hiển thị một con số khách không hề trả.
     */
    private function sales(): array
    {
        return collect(Setting::sales())
            ->map(fn($config, $method) => [
                'enabled' => (bool) $config['enabled'],
                'free_shipping' => (bool) $config['free_shipping']
                    || $config['fee_payer'] === Setting::PAYER_SHOP,
                'shipping_fee' => $config['fee_payer'] === Setting::PAYER_SHOP
                    ? 0
                    : max(0, (int) $config['shipping_fee']),
                'free_shipping_min_items' => $config['free_shipping_min_items'],
                'bank' => $method === 'bank_transfer' ? $config['bank'] : null,
            ])
            ->all();
    }

    private function categories(): array
    {
        // Trang cửa hàng chỉ dựa vào trạng thái do quản trị viên thiết lập.
        // Không đồng bộ theo số sản phẩm ở đây vì thao tác đó có thể tự chuyển
        // một danh mục "Đang dùng" thành ẩn ngay trước khi trả dữ liệu cho web.
        $categories = Categories::where('status', 1)
            ->withCount(['products' => fn($q) => $q->storefrontVisible()])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $byId = $categories->keyBy('id');
        $children = $categories->groupBy('parent_id');
        $ordered = [];
        $visited = [];
        $append = function (Categories $category) use (&$append, &$ordered, &$visited, $children): void {
            if (isset($visited[$category->id])) {
                return;
            }

            $visited[$category->id] = true;
            $ordered[] = $category;
            foreach ($children->get($category->id, []) as $child) {
                $append($child);
            }
        };

        // Nhánh gốc theo sort_order; con theo sort_order ngay sau cha. Nếu cha
        // không còn hiển thị, vẫn đưa danh mục con ra thay vì làm mất nó.
        foreach ($categories as $category) {
            if ($category->parent_id === null || ! $byId->has($category->parent_id)) {
                $append($category);
            }
        }
        foreach ($categories as $category) {
            $append($category);
        }

        return collect($ordered)
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'parent_id' => $c->parent_id,
                'image' => $c->image ? $this->url($c->image) : null,
                'description' => $c->description,
                'count' => $c->products_count,
                'link_url' => $c->link_url,
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
            'category_id' => $product->category?->id,
            'category_slug' => $product->category?->slug,
            'description' => $product->description,
            'material' => $product->material,
            'brand' => $product->brand,
            'audience' => $product->audience ?: 'Mọi khách hàng',
            // Hàng mới về trong 30 ngày, để web gắn nhãn "mới" bằng dữ liệu thật.
            'is_new' => $product->created_at?->gt(now()->subDays(30)) ?? false,
            // Ngày tạo thật, để web sắp "hàng mới về" theo đúng thứ tự nhập hàng.
            // Chỉ có cờ is_new thì mọi sản phẩm cũ hơn 30 ngày đều bằng điểm nhau
            // và thứ tự rơi về bảng chữ cái - thêm hàng mới cũng không đẩy lên đầu.
            'created_at' => $product->created_at?->toIso8601String(),
            // Lượt bán thật: chỉ đếm đơn đã hoàn thành.
            'sold' => (int) $product->productInvoices
                ->filter(fn($line) => $line->invoice?->order_status === \App\Models\Invoice::STATUS_COMPLETED)
                ->sum('quantity'),
            'price' => $price,
            // Chỉ có giá gạch ngang khi thực sự đang giảm giá.
            'compare_price' => $product->discount_percent !== null
                ? (int) $product->sell_price
                : null,
            // Phần trăm luôn tính từ giá gốc và giá khuyến mại đã lưu, bất kể
            // người quản trị nhập mức giảm theo tiền hay theo phần trăm.
            'discount_percent' => $product->discount_percent,
            'is_featured' => (bool) $product->is_featured,
            // Hàng không theo dõi tồn kho không bị chặn bởi số lượng, nhưng vẫn
            // cần ít nhất một biến thể vì checkout nhận variant_id bắt buộc.
            'manage_stock' => (bool) $product->manage_stock,
            'in_stock' => $product->variants->isNotEmpty()
                && (! $product->manage_stock || $product->variants->sum('quantity') > 0),
            'total_stock' => (int) $product->variants->sum('quantity'),
            // Web bán hàng dùng nhãn này để hiển thị đúng ngữ cảnh ngành hàng
            // (ví dụ Màu sắc/Kích thước hoặc Mùi hương/Quy cách).
            'variant_attribute_labels' => Product::normalizeVariantAttributeLabels(
                $product->variant_attribute_labels,
            ),
            'images' => $gallery->all(),
            'videos' => $videos->map(fn($v) => $this->url($v->path))->values()->all(),
            // Ảnh nằm một lần ở cấp mẫu; các biến thể chỉ trả style_id để tránh
            // lặp cùng URL hàng chục lần cho mọi tổ hợp màu/size.
            'styles' => $product->styles->map(fn($style) => [
                'id' => $style->id,
                'name' => $style->name,
                'image' => $style->image?->path
                    ? $this->url($style->image->path)
                    : ($pinned?->path ? $this->url($pinned->path) : null),
            ])->values()->all(),
            'variants' => $product->variants->map(fn($v) => [
                // Web bán hàng gửi id này lại khi đặt hàng, đừng đổi định dạng.
                'id' => $v->id,
                'style_id' => $v->product_style_id,
                'size' => $v->size,
                'color' => $v->color,
                'sku' => $v->sku,
                'stock' => (int) $v->quantity,
                // Có bán được dòng này không - đã tính cả cờ theo dõi tồn kho.
                'available' => ! $product->manage_stock || $v->quantity > 0,
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
