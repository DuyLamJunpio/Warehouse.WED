<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'link_url')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->string('link_url', 2048)->nullable();
            });
        }

        // Chỉ đổi giá trị mặc định cho các danh mục tạo mới; không đụng dữ liệu
        // status hiện có để không làm mất các danh mục đang hiển thị.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE categories ALTER COLUMN status SET DEFAULT 0');
        }
    }

    public function down(): void
    {
        // Không tự động xóa cột hoặc khôi phục default trong production.
    }
};
