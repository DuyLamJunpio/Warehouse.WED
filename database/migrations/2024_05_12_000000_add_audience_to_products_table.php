<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Đối tượng mặc của sản phẩm.
 * Web bán hàng dùng trường này làm bộ lọc, nếu suy đoán từ tên danh mục
 * thì lọc ra sai sản phẩm, nên phải là dữ liệu khai báo thật.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('audience')->default('Unisex')->after('brand');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('audience');
        });
    }
};
