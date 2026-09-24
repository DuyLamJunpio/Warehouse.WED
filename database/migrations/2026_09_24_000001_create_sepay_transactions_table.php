<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sepay_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->unique();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices');
            $table->string('order_code', 32)->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('account_number', 50);
            $table->string('result', 32);
            $table->timestamps();
        });
    }

    // Dữ liệu giao dịch đối soát phải được giữ lại kể cả khi rollback mã ứng dụng.
    public function down(): void
    {
    }
};
