<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('product_variants', 'is_paused')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->boolean('is_paused')->default(false);
            });
        }
    }

    public function down(): void
    {
        // Không tự động xóa cột trong môi trường production. Nếu cần rollback,
        // hãy thực hiện sau khi đã xác nhận không còn dữ liệu trạng thái tạm dừng.
    }
};
