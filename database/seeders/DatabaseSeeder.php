<?php

namespace Database\Seeders;

use App\Models\Categories;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /** Bảng size/màu dùng để sinh biến thể mẫu. */
    private const SIZES = ['S', 'M', 'L', 'XL'];
    private const COLORS = ['Đen', 'Trắng', 'Be'];

    public function run(): void
    {
        $this->seedUsers();
        $categories = $this->seedCategories();
        $supplier = $this->seedSupplier();
        $this->seedProducts($categories, $supplier);
        $this->seedCustomers();
    }

    private function seedUsers(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@warehouse.local'],
            [
                'name' => 'Administrator',
                // Mật khẩu lấy từ biến môi trường ADMIN_SEED_PASSWORD, mặc định 'password'.
                'password' => Hash::make(env('ADMIN_SEED_PASSWORD', 'password')),
                'role' => 1,
                'status' => 1,
                'email_verified_at' => now(),
            ]
        );
    }

    /**
     * @return array<string, Categories> danh mục con, key = slug
     */
    private function seedCategories(): array
    {
        $tree = [
            'Áo' => ['Áo thun', 'Áo sơ mi', 'Áo khoác'],
            'Quần' => ['Quần jean', 'Quần tây', 'Quần short'],
            'Váy - Đầm' => ['Váy ngắn', 'Đầm dự tiệc'],
            'Phụ kiện' => ['Túi xách', 'Thắt lưng'],
        ];

        $children = [];
        $order = 0;

        foreach ($tree as $parentName => $childNames) {
            $parent = Categories::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'sort_order' => $order++,
                'status' => 1,
            ]);

            foreach ($childNames as $i => $childName) {
                $child = Categories::create([
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'sort_order' => $i,
                    'status' => 1,
                ]);

                $children[$child->slug] = $child;
            }
        }

        return $children;
    }

    private function seedSupplier(): Supplier
    {
        return Supplier::create([
            'supplier_name' => 'Xưởng may Tân Bình',
            'supplier_phone' => '0901234567',
            'address' => '123 Trường Chinh, Tân Bình, TP.HCM',
            'tax' => '0312345678',
            'status' => 1,
        ]);
    }

    /**
     * @param array<string, Categories> $categories
     */
    private function seedProducts(array $categories, Supplier $supplier): void
    {
        $catalog = [
            ['Áo thun cotton basic', 'ao-thun', 85000, 159000, 'Cotton 100%'],
            ['Áo thun oversize form rộng', 'ao-thun', 95000, 189000, 'Cotton pha'],
            ['Áo sơ mi lụa tay dài', 'ao-so-mi', 150000, 299000, 'Lụa'],
            ['Áo khoác jean unisex', 'ao-khoac', 220000, 459000, 'Denim'],
            ['Quần jean ống suông', 'quan-jean', 180000, 389000, 'Denim co giãn'],
            ['Quần tây công sở', 'quan-tay', 160000, 329000, 'Kaki'],
            ['Quần short kaki', 'quan-short', 90000, 199000, 'Kaki'],
            ['Váy ngắn xếp ly', 'vay-ngan', 130000, 279000, 'Voan'],
            ['Đầm dự tiệc dáng xòe', 'dam-du-tiec', 350000, 750000, 'Voan lụa'],
            ['Túi tote canvas', 'tui-xach', 60000, 139000, 'Canvas'],
        ];

        foreach ($catalog as [$name, $categorySlug, $importPrice, $sellPrice, $material]) {
            $category = $categories[$categorySlug] ?? null;

            $product = Product::create([
                'supplier_id' => $supplier->id,
                'categories_id' => $category?->id,
                'product_name' => $name,
                'slug' => Str::slug($name),
                'barcode' => Str::uuid()->toString(),
                'description' => $name . ' - hàng thiết kế, chất liệu ' . strtolower($material) . '.',
                'material' => $material,
                'brand' => 'Phương Lê',
                'unit' => 'cái',
                'import_price' => $importPrice,
                'sell_price' => $sellPrice,
                'is_featured' => false,
                'status' => 1,
            ]);

            $this->seedVariants($product);
        }
    }

    private function seedVariants(Product $product): void
    {
        $sort = 0;

        foreach (self::SIZES as $size) {
            foreach (self::COLORS as $color) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'size' => $size,
                    'color' => $color,
                    'sku' => strtoupper(Str::slug($product->slug)) . '-' . $size . '-' . strtoupper(Str::slug($color)),
                    'quantity' => rand(0, 30),
                    'sort_order' => $sort++,
                ]);
            }
        }

        // Sản phẩm hết sạch tồn thì đánh dấu hết hàng.
        if ($product->variants()->sum('quantity') === 0) {
            $product->update(['status' => 2]);
        }
    }

    private function seedCustomers(): void
    {
        $customers = [
            ['Nguyễn Thị Mai', '0912345678', 'mai.nguyen@example.com', '45 Lê Lợi, Quận 1, TP.HCM'],
            ['Trần Văn Hùng', '0923456789', 'hung.tran@example.com', '78 Nguyễn Trãi, Quận 5, TP.HCM'],
            ['Lê Thị Hoa', '0934567890', 'hoa.le@example.com', '12 Cách Mạng Tháng 8, Quận 3, TP.HCM'],
        ];

        foreach ($customers as [$name, $phone, $email, $address]) {
            Customer::create([
                'customer_name' => $name,
                'customer_phone' => $phone,
                'customer_email' => $email,
                'address' => $address,
                'status' => 0,
            ]);
        }
    }
}
