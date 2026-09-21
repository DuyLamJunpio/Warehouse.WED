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
        'sort_order',
        'status',
    ];

    // Nếu bạn sử dụng kiểu ngày tháng carbon khác với mặc định
    protected $dates = ['deleted_at'];

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
     * Danh mục cha là công tắc của cả nhánh: khi cha đang tắt thì con cũng tắt;
     * khi cha bật, chỉ con có sản phẩm mới được bật. Cha không có sản phẩm nào
     * (kể cả trong con) cũng tự tắt. Cố ý chỉ cập nhật status, không xóa dữ liệu.
     */
    public static function syncVisibilityStatuses(): void
    {
        $categories = static::withCount('products')
            ->with(['children' => fn ($query) => $query->withCount('products')])
            ->get();

        DB::transaction(function () use ($categories): void {
            foreach ($categories->whereNull('parent_id') as $parent) {
                $children = $parent->children;
                if ($children->isEmpty()) {
                    $parent->setStatusFromProductCount((int) $parent->products_count > 0);
                    continue;
                }

                $hasProducts = (int) $parent->products_count > 0
                    || $children->contains(fn ($child) => (int) $child->products_count > 0);

                // Cha đang tắt là lựa chọn ẩn cả nhánh; không tự bật lại chỉ
                // vì một danh mục con vừa có hàng.
                if (!$hasProducts || (int) $parent->status === 0) {
                    $parent->setStatusFromProductCount(false);
                    foreach ($children as $child) {
                        $child->setStatusFromProductCount(false);
                    }
                    continue;
                }

                $parent->setStatusFromProductCount(true);
                foreach ($children as $child) {
                    $child->setStatusFromProductCount((int) $child->products_count > 0);
                }
            }
        });
    }

    private function setStatusFromProductCount(bool $visible): void
    {
        $status = $visible ? 1 : 0;
        if ((int) $this->status !== $status) {
            $this->forceFill(['status' => $status])->saveQuietly();
            $this->status = $status;
        }
    }
}
