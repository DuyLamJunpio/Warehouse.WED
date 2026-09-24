<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'visibility_override')) {
            Schema::table('categories', function (Blueprint $table): void {
                // null = tự xác định theo số sản phẩm; true/false = chủ shop chọn.
                $table->boolean('visibility_override')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Không tự xóa dữ liệu cấu hình hiển thị khi rollback production.
    }
};
