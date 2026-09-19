<?php

namespace Database\Seeders;

use App\Models\Categories;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

/**
 * Bộ catalogue thử nghiệm của RUNGU.
 *
 * Chỉ thêm hoặc cập nhật các slug/SKU có tiền tố `rungu-demo`, vì vậy chạy lại
 * seeder không làm đụng tới sản phẩm hay danh mục do người dùng tạo.
 */
class RunguDemoSeeder extends Seeder
{
    public function run(): void
    {
        $supplier = Supplier::firstOrCreate(
            ['tax' => 'RUNGU-DEMO'],
            [
                'supplier_name' => 'RUNGU — dữ liệu thử nghiệm',
                'supplier_phone' => '0900000000',
                'address' => 'Kho thử nghiệm RUNGU',
                'status' => 1,
            ],
        );

        $categories = collect([
            ['slug' => 'rungu-demo-tram-huong', 'name' => 'Trầm hương', 'description' => 'Trầm, nhang và phụ kiện xông hương.'],
            ['slug' => 'rungu-demo-nen-huong', 'name' => 'Nến hương', 'description' => 'Nến thơm cho những khoảng lặng thường nhật.'],
            ['slug' => 'rungu-demo-go-thanh', 'name' => 'Gỗ thánh', 'description' => 'Palo Santo và vật phẩm thanh tẩy không gian.'],
        ])->mapWithKeys(function (array $category, int $index): array {
            $model = Categories::firstOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'sort_order' => $index,
                    'status' => 1,
                ],
            );

            return [$category['slug'] => $model];
        });

        $catalogue = [
            [
                'slug' => 'rungu-demo-tram-nhang-khong-tam',
                'category' => 'rungu-demo-tram-huong',
                'name' => 'Nhang trầm hương không tăm — hộp 50 nén',
                'material' => 'Bột trầm tự nhiên và keo bời lời',
                'audience' => 'Thiền & thư giãn',
                'unit' => 'hộp',
                'import_price' => 165000,
                'sell_price' => 290000,
                'discount_price' => 260000,
                'variants' => [
                    ['size' => '50 nén', 'color' => 'Trầm hương', 'sku' => 'RUNGU-DEMO-TRAM-50', 'quantity' => 24],
                    ['size' => '100 nén', 'color' => 'Trầm hương', 'sku' => 'RUNGU-DEMO-TRAM-100', 'quantity' => 12],
                ],
            ],
            [
                'slug' => 'rungu-demo-nen-rung-suong-mu',
                'category' => 'rungu-demo-nen-huong',
                'name' => 'Nến hương Rừng Sương Mù — hũ 180g',
                'material' => 'Sáp đậu nành, bấc gỗ và tinh dầu tuyết tùng',
                'audience' => 'Mọi không gian',
                'unit' => 'hũ',
                'import_price' => 190000,
                'sell_price' => 350000,
                'discount_price' => null,
                'variants' => [
                    ['size' => '180g', 'color' => 'Tuyết tùng', 'sku' => 'RUNGU-DEMO-NEN-CEDAR', 'quantity' => 18],
                    ['size' => '180g', 'color' => 'Trầm hương', 'sku' => 'RUNGU-DEMO-NEN-OUD', 'quantity' => 15],
                ],
            ],
            [
                'slug' => 'rungu-demo-palo-santo-set-5',
                'category' => 'rungu-demo-go-thanh',
                'name' => 'Gỗ thánh Palo Santo — set 5 thanh',
                'material' => 'Palo Santo rụng tự nhiên, thu hái có trách nhiệm',
                'audience' => 'Nghi lễ & thờ cúng',
                'unit' => 'set',
                'import_price' => 105000,
                'sell_price' => 180000,
                'discount_price' => null,
                'variants' => [
                    ['size' => '3 thanh', 'color' => 'Palo Santo', 'sku' => 'RUNGU-DEMO-PALO-3', 'quantity' => 20],
                    ['size' => '5 thanh', 'color' => 'Palo Santo', 'sku' => 'RUNGU-DEMO-PALO-5', 'quantity' => 16],
                ],
            ],
        ];

        foreach ($catalogue as $index => $item) {
            $product = Product::firstOrNew(['slug' => $item['slug']]);
            $product->fill([
                'supplier_id' => $supplier->id,
                'categories_id' => $categories[$item['category']]->id,
                'product_name' => $item['name'],
                'barcode' => 'RUNGU-DEMO-' . ($index + 1),
                'description' => $item['material'] . '. Sản phẩm mẫu để kiểm thử luồng quản trị RUNGU.',
                'material' => $item['material'],
                'brand' => 'RUNGU',
                'audience' => $item['audience'],
                'unit' => $item['unit'],
                'import_price' => $item['import_price'],
                'sell_price' => $item['sell_price'],
                'discount_price' => $item['discount_price'],
                'is_featured' => true,
                'manage_stock' => true,
                'status' => 1,
            ]);
            $product->save();

            foreach ($item['variants'] as $variantIndex => $variant) {
                ProductVariant::updateOrCreate(
                    ['sku' => $variant['sku']],
                    [
                        'product_id' => $product->id,
                        'size' => $variant['size'],
                        'color' => $variant['color'],
                        'quantity' => $variant['quantity'],
                        'sort_order' => $variantIndex,
                    ],
                );
            }
        }
    }
}
