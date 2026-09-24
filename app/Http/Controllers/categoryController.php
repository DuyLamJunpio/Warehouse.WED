<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Services\StorefrontNotifier;

class categoryController extends Controller
{
    /** Cây danh mục chỉ 2 cấp: danh mục gốc -> danh mục con. */
    private const MAX_DEPTH = 2;

    private const PER_PAGE = 10;

    public function __construct(private StorefrontNotifier $notifier)
    {
    }

    public function index()
    {
        return view('categories.index', $this->listData());
    }

    public function getData()
    {
        return view('categories.data', $this->listData());
    }

    /**
     * Danh sách phân trang theo danh mục gốc, mỗi gốc kèm danh mục con.
     * Phân trang áp lên danh mục gốc để danh mục con không bị cắt rời khỏi cha.
     */
    private function listData(?string $keyword = null): array
    {
        Categories::syncVisibilityStatuses();
        $query = Categories::roots()
            ->with(['children' => fn($q) => $q->withCount('products')])
            ->withCount('products')
            ->orderBy('sort_order');

        if (!empty($keyword)) {
            // Khớp tên danh mục gốc hoặc tên danh mục con, để tìm "Áo thun" vẫn ra nhánh "Áo".
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'ilike', "%{$keyword}%")
                    ->orWhereHas('children', fn($c) => $c->where('name', 'ilike', "%{$keyword}%"));
            });
        }

        return [
            'categories' => $query->paginate(self::PER_PAGE),
            'parentOptions' => Categories::roots()->orderBy('sort_order')->get(['id', 'name']),
            'linkOptions' => $this->linkOptions(),
        ];
    }

    public function store(Request $request)
    {
        if (!$request->ajax() && !$request->wantsJson()) {
            abort(404);
        }

        $data = $this->validated($request);

        // Danh mục mới ẩn mặc định, chủ shop chủ động bật trong phần chỉnh sửa.
        $data['status'] = array_key_exists('status', $data) && $data['status'] !== null
            ? (int) $data['status']
            : 0;
        // Ẩn mặc định vẫn là chế độ tự động: khi sau này có sản phẩm, danh
        // mục sẽ tự được bật. Chỉ trạng thái "đang hiện" lúc tạo mới mới là
        // một lựa chọn bật thủ công cho danh mục rỗng.
        $data['visibility_override'] = (int) $data['status'] === 1 ? true : null;

        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['sort_order'] = $this->nextSortOrder($data['parent_id'] ?? null);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('public/images');
        }

        $category = Categories::create($data);

        if (!$category->id) {
            return response()->json(['error' => 'Chưa thể tạo danh mục. Danh mục chưa được lưu; vui lòng kiểm tra thông tin rồi thử lại.'], 500);
        }

        $this->notifier->markDirty();

        return response()->json([
            'success' => 'Danh mục đã được thêm thành công!',
            // Trả về để JS bổ sung ngay vào ô chọn "danh mục cha" mà không cần tải lại trang.
            'category' => $category->only(['id', 'name', 'parent_id']),
        ]);
    }

    public function edit(Request $request, string $id)
    {
        if (!$request->ajax() && !$request->wantsJson()) {
            abort(404);
        }

        $category = Categories::findOrFail($id);
        $data = $this->validated($request, $category);

        // Danh mục đang có con thì không được biến thành danh mục con (giới hạn 2 cấp).
        if (!empty($data['parent_id']) && $category->children()->exists()) {
            return response()->json([
                'error' => 'Danh mục này đang có danh mục con nên không thể chuyển thành danh mục con.',
            ], 422);
        }

        if ($category->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($data['name'], $category->id);
        }

        // Đổi cha thì xếp xuống cuối nhánh mới, tránh trùng sort_order với anh em sẵn có.
        if (($data['parent_id'] ?? null) !== $category->parent_id) {
            $data['sort_order'] = $this->nextSortOrder($data['parent_id'] ?? null);
        }

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::delete($category->image);
            }
            $data['image'] = $request->file('image')->store('public/images');
        }

        $requestedStatus = (int) ($data['status'] ?? $category->status);
        $statusChanged = $requestedStatus !== (int) $category->status;

        // Form luôn gửi cả status, kể cả khi chỉ sửa tên/mô tả. Chỉ tạo lựa
        // chọn thủ công khi người dùng thực sự đổi trạng thái; nếu không một
        // lần sửa mô tả có thể vô tình tắt cơ chế tự động theo sản phẩm.
        if ($statusChanged) {
            $data['visibility_override'] = $requestedStatus === 1;
        }

        $updated = DB::transaction(function () use ($category, $data, $requestedStatus, $statusChanged): bool {
            if (!$category->update($data)) {
                return false;
            }

            // Gạt trạng thái ở danh mục cha áp dụng cho cả nhánh. Khi bật lại,
            // chỉ con có sản phẩm được bật; con rỗng để null để sau này tự bật
            // ngay khi có sản phẩm.
            if ($statusChanged && $category->children()->exists()) {
                $children = $category->children()
                    ->withCount(['products' => fn ($query) => $query->where('status', '!=', 0)])
                    ->get();
                foreach ($children as $child) {
                    $visible = $requestedStatus === 1 && (int) $child->products_count > 0;
                    $child->update([
                        'status' => $visible ? 1 : 0,
                        'visibility_override' => $requestedStatus === 0
                            ? false
                            : null,
                    ]);
                }
            }

            return true;
        });

        if (! $updated) {
            return response()->json(['error' => 'Chưa thể cập nhật danh mục. Các thay đổi chưa được lưu; vui lòng thử lại.'], 500);
        }

        // Đổi trạng thái danh mục phải xoá cache catalogue ngay: nếu không web
        // bán hàng có thể còn hiện cả nhánh sản phẩm cũ tới hết chu kỳ cache.
        $this->notifier->markDirty();

        return response()->json(['success' => 'Danh mục đã được sửa thành công!']);
    }

    public function search(Request $request)
    {
        // Không cache: danh mục sửa xong phải thấy ngay, cache theo từ khóa sẽ trả về dữ liệu cũ.
        return view('categories.data', $this->listData(trim((string) $request->input('keyword'))));
    }

    public function destroy(string $id, Categories $categories)
    {
        $this->authorize('delete', $categories);

        $category = Categories::withCount(['products', 'children'])->findOrFail($id);

        if ($category->children_count > 0) {
            return response()->json([
                'error' => 'Danh mục còn ' . $category->children_count . ' danh mục con, hãy xóa hoặc chuyển chúng trước.',
            ], 422);
        }

        if ($category->products_count > 0) {
            return response()->json([
                'error' => 'Danh mục còn ' . $category->products_count . ' sản phẩm, hãy chuyển sản phẩm sang danh mục khác trước.',
            ], 422);
        }

        $category->delete();
        $this->notifier->markDirty();

        return response()->json(['success' => 'Danh mục đã được xóa thành công!']);
    }

    /**
     * Đổi thứ tự hiển thị: hoán vị sort_order với danh mục liền kề cùng cấp.
     */
    public function reorder(Request $request, string $id)
    {
        $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);

        $category = Categories::findOrFail($id);
        $isUp = $request->input('direction') === 'up';

        $neighbour = Categories::where('parent_id', $category->parent_id)
            ->where('sort_order', $isUp ? '<' : '>', $category->sort_order)
            ->orderBy('sort_order', $isUp ? 'desc' : 'asc')
            ->first();

        if (!$neighbour) {
            return response()->json(['error' => 'Danh mục đã ở vị trí đầu/cuối.'], 422);
        }

        [$category->sort_order, $neighbour->sort_order] = [$neighbour->sort_order, $category->sort_order];
        $category->save();
        $neighbour->save();
        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã cập nhật thứ tự.']);
    }

    /**
     * @param Categories|null $current danh mục đang sửa, dùng để loại chính nó khỏi danh sách cha hợp lệ
     */
    private function validated(Request $request, ?Categories $current = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->whereNull('deleted_at'),
                // Cha phải là danh mục gốc và không được là chính nó.
                function ($attribute, $value, $fail) use ($current) {
                    if ($current && (int) $value === $current->id) {
                        return $fail('Không thể chọn chính danh mục này làm danh mục cha.');
                    }
                    if (Categories::whereKey($value)->whereNotNull('parent_id')->exists()) {
                        $fail('Chỉ được chọn danh mục gốc làm danh mục cha (tối đa ' . self::MAX_DEPTH . ' cấp).');
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'link_url' => [
                'nullable',
                'string',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $value !== ''
                        && !str_starts_with((string) $value, '/')
                        && !filter_var($value, FILTER_VALIDATE_URL)) {
                        $fail('Liên kết phải là đường dẫn nội bộ bắt đầu bằng / hoặc URL đầy đủ (https://...).');
                    }
                },
            ],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['nullable', Rule::in([0, 1])],
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'danh-muc';
        $slug = $base;
        $suffix = 2;

        while (
            Categories::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    private function nextSortOrder(?int $parentId): int
    {
        return (int) Categories::where('parent_id', $parentId)->max('sort_order') + 1;
    }

    private function linkOptions(): array
    {
        $options = [
            ['label' => 'Trang chủ', 'url' => '/'],
            ['label' => 'Tất cả sản phẩm', 'url' => '/shop'],
            ['label' => 'Sản phẩm mới', 'url' => '/shop?new=1'],
            ['label' => 'Đang khuyến mãi', 'url' => '/shop?sale=1'],
            ['label' => 'In áo theo yêu cầu', 'url' => '/in-ao'],
            ['label' => 'Khối danh mục trên trang chủ', 'url' => '/#categories'],
        ];

        $categoryLinks = Categories::where('status', 1)
            ->orderBy('sort_order')
            ->get(['name'])
            ->map(fn (Categories $category) => [
                'label' => 'Danh mục: ' . $category->name,
                'url' => '/shop?category=' . rawurlencode($category->name),
            ])
            ->all();

        return array_merge($options, $categoryLinks);
    }
}
