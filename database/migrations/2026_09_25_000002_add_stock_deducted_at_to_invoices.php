<?php

use App\Models\Invoice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->timestamp('stock_deducted_at')->nullable()->after('payment_expires_at');
        });

        // Trước thay đổi này mọi đơn bán đều trừ kho ngay khi tạo. Đánh dấu các
        // đơn cũ đã trừ để việc hủy/hoàn sau deploy không cộng kho lần nữa.
        DB::table('invoices')
            ->where('invoice_type', Invoice::TYPE_ORDER)
            ->whereNull('stock_deducted_at')
            ->update(['stock_deducted_at' => DB::raw('created_at')]);
    }

    // Không xóa cột này khi rollback để không làm sai số tồn của đơn đã xử lý.
    public function down(): void
    {
    }
};
