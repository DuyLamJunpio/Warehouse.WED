<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cấu hình vận hành, lưu theo cặp khoá - giá trị JSON.
 *
 * Một bảng chung thay vì mỗi nhóm cài đặt một bảng: các nhóm này đều chỉ có
 * đúng một dòng, và mỗi lần thêm một tuỳ chọn mà phải chạy migration đổi cột
 * thì cài đặt sẽ không bao giờ được sửa cho kịp nhu cầu bán hàng.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
