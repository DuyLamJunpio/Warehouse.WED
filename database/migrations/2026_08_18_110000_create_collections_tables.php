<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bộ sưu tập do chủ shop tự chọn sản phẩm.
 *
 * Trước đây khối "bộ sưu tập theo mùa" lấy đại mấy sản phẩm đầu danh sách, mà
 * lấy đúng bằng danh sách của khối "hàng mới về" nên hai khối hiện trùng hàng.
 * Giờ chủ shop tự tích: mùa đông tích đồ đông, hè tích đồ hè, và tự đặt tên.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('image_path')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_link')->nullable();

            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);
            // Hẹn giờ như banner: chuẩn bị trước bộ sưu tập Tết rồi để nó tự lên.
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });

        Schema::create('collection_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);

            // Một sản phẩm chỉ nằm một lần trong cùng bộ sưu tập.
            $table->unique(['collection_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_product');
        Schema::dropIfExists('collections');
    }
};
