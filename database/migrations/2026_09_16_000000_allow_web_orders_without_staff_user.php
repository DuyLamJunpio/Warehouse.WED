<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Đơn từ storefront không do nhân viên đăng nhập tạo. Cho phép user_id null
     * để checkout vẫn hoạt động trước khi cửa hàng lập tài khoản quản trị đầu tiên.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE invoices ALTER COLUMN user_id DROP NOT NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Chỉ siết lại khi mọi đơn đều đã được gán nhân viên, tránh làm mất dữ liệu.
        if (! DB::table('invoices')->whereNull('user_id')->exists()) {
            DB::statement('ALTER TABLE invoices ALTER COLUMN user_id SET NOT NULL');
        }
    }
};
