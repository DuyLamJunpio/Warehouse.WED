<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phân biệt sản phẩm đơn và sản phẩm có biến thể.
 *
 * Sản phẩm đơn vẫn giữ một SKU nội bộ để không phá luồng bán hàng/tồn kho,
 * nhưng SKU đó không còn được trình bày như "Mặc định / Mặc định" trên UI.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products') || Schema::hasColumn('products', 'variant_mode')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            $table->string('variant_mode', 20)->default('simple')->after('manage_stock');
        });

        // Dữ liệu cũ thường có đúng một dòng placeholder "Mặc định". Chỉ
        // đánh dấu variable khi có nhiều SKU hoặc có giá trị lựa chọn thật.
        $variableIds = DB::table('product_variants')
            ->select('product_id')
            ->groupBy('product_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('product_id');

        $attributeIds = DB::table('product_variants')
            ->where(function ($query): void {
                $query->where(function ($value): void {
                    $value->whereNotNull('color')
                        ->whereRaw("LOWER(TRIM(color)) NOT IN (?, ?)", ['', 'mặc định']);
                })->orWhere(function ($value): void {
                    $value->whereNotNull('size')
                        ->whereRaw("LOWER(TRIM(size)) NOT IN (?, ?)", ['', 'mặc định']);
                });
            })
            ->pluck('product_id');

        $variableIds = $variableIds->merge($attributeIds)->unique()->values();
        if ($variableIds->isNotEmpty()) {
            DB::table('products')
                ->whereIn('id', $variableIds)
                ->update(['variant_mode' => 'variable']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'variant_mode')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('variant_mode');
            });
        }
    }
};
