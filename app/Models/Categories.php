<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Categories extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'image',
        'description',
        'link_url',
        'visibility_override',
        'sort_order',
        'status',
    ];

    // Nếu bạn sử dụng kiểu ngày tháng carbon khác với mặc định
    protected $dates = ['deleted_at'];

    protected $casts = [
        'visibility_override' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'categories_id');
    }

    public function parent()
    {
        return $this->belongsTo(Categories::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Categories::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Chỉ lấy danh mục gốc (không có cha).
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Đồng bộ trạng thái hiển thị theo số sản phẩm trong cây danh mục.
     *
     * Danh mục cha là công tắc của cả nhánh: khi cha đang tắt thì con cũng tắt.
     * Sản phẩm ngưng bán không được tính, để một ô danh mục không dẫn khách tới
     * trang kết quả rỗng. `visibility_override` là lựa chọn thủ công của chủ
     * shop (ví dụ Đồng Phục dẫn sang trang in áo), nên danh mục rỗng được bật
     * thủ công không bị đồng bộ tự tắt.
     */
    public static function syncVisibilityStatuses(): void
    {
        $visibleProducts = fn ($query) => $query->where('status', '!=', 0);

        $categories = static::withCount(['products' => $visibleProducts])
            ->with(['children' => fn ($query) => $query->withCount(['products' => $visibleProducts])])
            ->get();

        DB::transaction(function () use ($categories): void {
            foreach ($categories->whereNull('parent_id') as $parent) {
                $children = $parent->children;
                if ($children->isEmpty()) {
                    $parent->setVisibilityStatus($parent->resolvedVisibility((int) $parent->products_count > 0));
                    continue;
                }

                // Một danh mục con rỗng có thể được chủ shop chủ động bật để
                // dẫn tới một trang riêng. Khi đó danh mục cha cũng cần hiện
                // ra, nếu không webstore không thể tới được danh mục con đó.
                $hasVisibleContent = (int) $parent->products_count > 0
                    || $children->contains(fn ($child) => $child->resolvedVisibility(
                        (int) $child->products_count > 0
                    ));

                $parentVisible = $parent->resolvedVisibility($hasVisibleContent);
                if (!$parentVisible) {
                    $parent->setVisibilityStatus(false);
                    foreach ($children as $child) {
                        $child->setVisibilityStatus(false);
                    }
                    continue;
                }

                $parent->setVisibilityStatus(true);
                foreach ($children as $child) {
                    $child->setVisibilityStatus($child->resolvedVisibility((int) $child->products_count > 0));
                }
            }
        });
    }

    private function resolvedVisibility(bool $automatic): bool
    {
        return $this->visibility_override === null
            ? $automatic
            : (bool) $this->visibility_override;
    }

    private function setVisibilityStatus(bool $visible): void
    {
        $status = $visible ? 1 : 0;
        if ((int) $this->status !== $status) {
            $this->forceFill(['status' => $status])->saveQuietly();
            $this->status = $status;
        }
    }
}
