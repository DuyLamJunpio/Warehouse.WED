<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Biến thể sản phẩm (size x màu) - đơn vị giữ tồn kho của shop quần áo.
 *
 * Thay thế bảng `expiries` của hệ thống kho cũ: quần áo không có hạn sử dụng,
 * nhưng vẫn cần một bảng con giữ số lượng tồn để tổng hợp bằng withSum().
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('size')->nullable();   // S, M, L, XL, 28, 29...
            $table->string('color')->nullable();  // Đen, Trắng, Be...
            $table->string('sku')->unique();
            $table->integer('quantity')->default(0);
            $table->bigInteger('price_override')->nullable(); // null = dùng giá của sản phẩm
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Không cho trùng cùng một tổ hợp size + màu trong một sản phẩm
            $table->unique(['product_id', 'size', 'color']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
