<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Giảm giá riêng của từng phôi: theo % hoặc theo số tiền mỗi áo.
     *
     * Hai cột cùng rỗng nghĩa là không giảm, nên mọi phôi đang bán giữ nguyên giá
     * sau khi chạy migration này.
     */
    public function up(): void
    {
        Schema::table('print_blanks', function (Blueprint $table) {
            $table->string('discount_type', 10)->nullable()->after('base_price');
            $table->unsignedInteger('discount_value')->nullable()->after('discount_type');
        });
    }

    /**
     * Không tự xoá cột khi rollback để bảo toàn mức giảm đã khai.
     */
    public function down(): void
    {
        // Intentionally left blank: project data must not be dropped automatically.
    }
};
