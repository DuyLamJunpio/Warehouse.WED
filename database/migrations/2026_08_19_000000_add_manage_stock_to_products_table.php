<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sản phẩm có theo dõi tồn kho hay không.
 *
 * Hàng đặt may, hàng order hay đồ nhận từ xưởng theo yêu cầu thì số tồn không
 * nói lên điều gì: kho ghi 0 nhưng vẫn bán được. Không có cờ này thì cách duy
 * nhất để bán tiếp là nhập một con số tồn giả, và từ đó mọi báo cáo kho đều sai.
 *
 * Mặc định bật để toàn bộ hàng đang có giữ nguyên cách chạy cũ.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('manage_stock')->default(true)->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('manage_stock');
        });
    }
};
