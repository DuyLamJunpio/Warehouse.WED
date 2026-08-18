<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hạn thanh toán của đơn đặt từ web bán hàng.
 *
 * Mã QR của PayOS hết hạn sau 30 phút (PAYMENT_WINDOW_MINUTES bên webstore).
 * Quá hạn mà chưa nhận được tiền thì đơn phải tự huỷ và trả hàng về kho —
 * nếu không, hàng bị giữ mãi bởi những đơn khách đã bỏ ngang.
 *
 * Null nghĩa là "không có hạn": đơn COD và đơn bán tại quầy không bao giờ bị
 * tự huỷ theo cơ chế này.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('payment_expires_at')->nullable()->after('payment_method');

            // Lệnh quét chạy mỗi phút, luôn lọc theo đúng ba cột này.
            $table->index(
                ['order_status', 'pay_status', 'payment_expires_at'],
                'invoices_expiry_sweep_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_expiry_sweep_index');
            $table->dropColumn('payment_expires_at');
        });
    }
};
