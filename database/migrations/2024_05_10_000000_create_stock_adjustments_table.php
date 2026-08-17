<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nhật ký điều chỉnh tồn kho thủ công (kiểm kê, hàng lỗi, thất thoát...).
 * Mỗi lần sửa tay số lượng của một biến thể đều phải để lại vết ở đây.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('quantity_before');
            $table->integer('quantity_change'); // âm = giảm tồn, dương = tăng tồn
            $table->integer('quantity_after');
            $table->string('reason');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['variant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
