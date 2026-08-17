<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bổ sung các trường khách nhập ở bước thanh toán của web bán hàng.
 * Khách không có tài khoản, nên số điện thoại là thứ dùng để nhận diện.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('province')->nullable()->after('address');  // Tỉnh / Thành phố
            $table->string('ward')->nullable()->after('province');     // Phường / Xã
            $table->text('note')->nullable()->after('ward');           // Ghi chú chăm sóc khách hàng

            // Gộp khách trùng theo số điện thoại nên cột này được tra cứu liên tục.
            $table->index('customer_phone');
        });

        // Email có thể bỏ trống: số điện thoại mới là thứ bắt buộc.
        // Dùng SQL thẳng vì đổi cột bằng ->change() cần doctrine/dbal.
        DB::statement('ALTER TABLE customers ALTER COLUMN customer_email DROP NOT NULL');
        DB::statement('ALTER TABLE customers ALTER COLUMN address DROP NOT NULL');
        DB::statement('ALTER TABLE customers ALTER COLUMN status SET DEFAULT 0');
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['customer_phone']);
            $table->dropColumn(['province', 'ward', 'note']);
        });

        DB::statement('ALTER TABLE customers ALTER COLUMN customer_email SET NOT NULL');
        DB::statement('ALTER TABLE customers ALTER COLUMN address SET NOT NULL');
    }
};
