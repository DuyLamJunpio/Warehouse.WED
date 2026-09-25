<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phiên tạo VietQR tách biệt hoàn toàn với Invoice.
     *
     * Một phiên chỉ xuất hiện ở giao diện khách trong tối đa 15 phút. Khi SePay
     * báo tiền vào, webhook dùng payload đã chốt ở đây để tạo một Invoice đã
     * thanh toán; trước thời điểm đó quản trị không có đơn chờ thanh toán.
     */
    public function up(): void
    {
        Schema::create('storefront_payment_sessions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('checkout_ref')->unique();
            $table->string('checkout_fingerprint', 64);
            $table->string('payment_code', 32)->unique();
            $table->json('payload');
            $table->unsignedBigInteger('total_amount');
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('expires_at')->index();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('invoice_id')->nullable()->unique()->constrained('invoices')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_payment_sessions');
    }
};
