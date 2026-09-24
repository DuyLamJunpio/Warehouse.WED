<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Size được khai riêng theo từng màu của một phôi.
     *
     * Nullable là chủ ý: các màu đã có giữ nguyên ý nghĩa cũ, tức dùng mọi
     * size của sản phẩm nối kho (hoặc "Một cỡ" với phôi không nối kho).
     */
    public function up(): void
    {
        Schema::table('print_blank_colors', function (Blueprint $table) {
            $table->json('sizes')->nullable()->after('tone');
        });
    }

    /**
     * Không tự xoá cột khi rollback để bảo toàn dữ liệu size đã khai.
     */
    public function down(): void
    {
        // Intentionally left blank: project data must not be dropped automatically.
    }
};
