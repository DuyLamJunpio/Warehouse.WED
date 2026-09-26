<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sổ cái tồn kho: mọi thay đổi tự động sau khi migration này được áp dụng
     * đều có thể truy ngược về đơn hàng hoặc thao tác của nhân viên.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 30);
            $table->integer('quantity_before');
            // Âm là xuất kho, dương là nhập/trả kho.
            $table->integer('quantity_change');
            $table->integer('quantity_after');
            $table->string('reason')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['variant_id', 'created_at']);
            $table->index(['invoice_id', 'type']);
        });
    }

    // Không xóa sổ cái khi rollback để tránh mất lịch sử tồn kho đã ghi nhận.
    public function down(): void
    {
    }
};
