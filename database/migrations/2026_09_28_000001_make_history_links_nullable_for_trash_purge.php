<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lịch sử bán hàng/tồn kho phải sống tiếp khi sản phẩm hoặc biến thể được
 * purge khỏi thùng rác. Khi đó chỉ bỏ liên kết tới catalogue đã mất.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->makeNullable('product_invoices', 'product_id', 'products');
        $this->makeNullable('stock_adjustments', 'variant_id', 'product_variants');
        $this->makeNullable('stock_movements', 'variant_id', 'product_variants');
        $this->makeNullable('sepay_transactions', 'invoice_id', 'invoices');
    }

    public function down(): void
    {
        // Không khôi phục NOT NULL tự động: dữ liệu lịch sử có thể đã có liên
        // kết null sau khi purge, và ép ngược sẽ làm mất khả năng rollback an toàn.
    }

    private function makeNullable(string $tableName, string $column, string $referencedTable): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $column)) {
            return;
        }

        // SQLite không hỗ trợ dropForeign trên bảng đang tồn tại. Production
        // của nhánh này dùng PostgreSQL; test SQLite vẫn chạy được migration
        // xóa mềm và không cần thay đổi constraint lịch sử của production.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column): void {
            $table->dropForeign([$column]);
        });

        Schema::table($tableName, function (Blueprint $table) use ($column, $referencedTable): void {
            $table->unsignedBigInteger($column)->nullable()->change();
            $table->foreign($column)->references('id')->on($referencedTable)->nullOnDelete();
        });
    }
};
