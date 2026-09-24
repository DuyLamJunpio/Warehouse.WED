<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_techniques', function (Blueprint $table) {
            // Không tự chọn một giá từ ma trận cũ. Shop nhập giá cố định mới.
            $table->unsignedBigInteger('price')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('print_techniques', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
