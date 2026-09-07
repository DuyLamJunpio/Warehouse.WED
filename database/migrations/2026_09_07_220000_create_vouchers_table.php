<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Bảng độc lập, chỉ thêm mới và không thay đổi bất kỳ bảng dữ liệu cũ nào. */
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            // percentage | fixed_amount | free_shipping
            $table->string('type', 30);
            $table->unsignedBigInteger('value')->default(0);
            $table->unsignedBigInteger('max_discount')->nullable();
            $table->unsignedBigInteger('min_order_amount')->default(0);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('status')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['status', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        // Không drop bảng để bảo toàn dữ liệu voucher khi rollback ngoài ý muốn.
    }
};
