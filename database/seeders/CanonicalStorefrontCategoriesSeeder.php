<?php

namespace Database\Seeders;

use App\Models\Categories;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Bổ sung cây danh mục chuẩn của web bán hàng mà không đụng tới dữ liệu khác.
 * Chạy riêng bằng --class=CanonicalStorefrontCategoriesSeeder khi cần đồng bộ.
 */
class CanonicalStorefrontCategoriesSeeder extends Seeder
{
    private const ROOTS = [
        'sang-tao' => ['Sáng tạo', 'Tác phẩm nghệ nhân và sáng tạo độc bản'],
        'huong-thom' => ['Hương thơm', 'Nến thơm tự nhiên và xô thơm thảo mộc'],
        'go-hoa-co' => ['Gỗ hoa cỏ', 'Gỗ thánh Palo Santo và trầm nén tự nhiên'],
        'dat-va-da' => ['Đất và Đá', 'Khay gốm thủ công men tro nung củi nhiệt cao'],
        'phu-kien' => ['Phụ kiện', 'Vòng tay bách xanh và dụng cụ nghi thức mộc'],
        'qua-tang' => ['Quà tặng', 'Những hộp quà trang nhã gói ghém sự an yên'],
    ];

    /** Danh mục demo đang chứa sản phẩm thật; giữ nguyên sản phẩm và xếp dưới nhánh chuẩn. */
    private const DEMO_PARENTS = [
        'rungu-demo-tram-huong' => 'huong-thom',
        'rungu-demo-nen-huong' => 'huong-thom',
        'rungu-demo-go-thanh' => 'go-hoa-co',
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $demoCategories = [];
            foreach (self::DEMO_PARENTS as $slug => $parentSlug) {
                $category = Categories::where('slug', $slug)->first();
                if (! $category) {
                    continue;
                }

                // Admin chỉ hỗ trợ 2 cấp. Kiểm tra trước khi ghi bất kỳ bản ghi
                // nào để dữ liệu bất ngờ không làm phát sinh cây 3 cấp.
                if ($category->children()->exists()) {
                    throw new RuntimeException("Danh mục {$slug} đang có danh mục con; không thể chuyển vào {$parentSlug}.");
                }
                $demoCategories[$slug] = $category;
            }

            // Các nhánh demo sẽ thành danh mục con. Chỉ so với danh mục gốc khác
            // để sáu nhánh chuẩn hiện trước mà không đổi thứ tự của chúng.
            $firstOtherOrder = Categories::roots()
                ->where('status', 1)
                ->whereNotIn('slug', array_merge(array_keys(self::ROOTS), array_keys(self::DEMO_PARENTS)))
                ->min('sort_order');
            $firstOrder = $firstOtherOrder === null
                ? 0
                : (int) $firstOtherOrder - count(self::ROOTS);

            $roots = [];
            foreach (self::ROOTS as $slug => [$name, $description]) {
                // withTrashed tránh trùng unique slug và khôi phục đúng bản ghi cũ.
                $category = Categories::withTrashed()->firstOrNew(['slug' => $slug]);
                $category->fill([
                    'name' => $name,
                    'parent_id' => null,
                    'status' => 1,
                    'sort_order' => $firstOrder++,
                ]);
                // Giữ nguyên lời giới thiệu đã được chỉnh trong trang quản trị.
                if ($category->description === null || trim($category->description) === '') {
                    $category->description = $description;
                }
                if ($category->trashed()) {
                    $category->deleted_at = null;
                }
                if (! $category->exists || $category->isDirty()) {
                    $category->save();
                }
                $roots[$slug] = $category;
            }

            foreach ($demoCategories as $slug => $category) {
                $parentId = $roots[self::DEMO_PARENTS[$slug]]->id;
                if ((int) $category->parent_id !== $parentId) {
                    $category->parent_id = $parentId;
                    $category->save();
                }
            }
        });
    }
}
