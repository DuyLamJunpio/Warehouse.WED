<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Thành phần cấu thành một biến thể combo. */
    public function up(): void
    {
        Schema::create('product_combo_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('combo_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('component_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->unique(['combo_variant_id', 'component_variant_id']);
            $table->index('component_variant_id');
        });

        Schema::table('products', function (Blueprint $table): void {
            // Combo không có tồn độc lập; tồn bán được được tính từ các thành phần.
            $table->boolean('is_combo')->default(false)->after('manage_stock');
        });
    }

    // Giữ cấu trúc combo và dữ liệu đã khai báo khi rollback.
    public function down(): void
    {
    }
};
