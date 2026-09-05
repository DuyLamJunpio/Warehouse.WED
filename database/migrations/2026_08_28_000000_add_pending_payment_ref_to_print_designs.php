<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Khóa một mẫu in vào một phiên QR còn hiệu lực.
 *
 * Khóa này không phải đơn hàng và không được hiển thị trên màn hình duyệt thiết kế.
 * Nó chỉ ngăn cùng một mẫu bị thanh toán hai lần qua hai mã QR khác nhau trong
 * khoảng thời gian chờ chuyển khoản.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_designs', function (Blueprint $table) {
            $table->string('pending_payment_ref', 32)->nullable()->after('invoice_id')->index();
        });
    }

    // Cột này giữ liên kết thanh toán phục vụ tra soát. Không xóa dữ liệu bằng rollback tự động.
    public function down(): void
    {
    }
};
