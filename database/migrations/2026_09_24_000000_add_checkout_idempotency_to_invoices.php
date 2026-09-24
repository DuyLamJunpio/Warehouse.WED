<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->uuid('checkout_ref')->nullable()->unique();
            $table->char('checkout_fingerprint', 64)->nullable();
        });
    }

    // Giữ dữ liệu đơn và khóa chống tạo trùng khi rollback mã ứng dụng.
    public function down(): void
    {
    }
};
