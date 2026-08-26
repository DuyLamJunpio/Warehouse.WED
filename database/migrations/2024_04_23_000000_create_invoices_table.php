<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->bigInteger('total_amount');
            // 0 = phiếu nhập hàng từ nhà cung cấp, 1 = đơn hàng bán cho khách
            $table->integer('invoice_type');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->integer('pay_status');
            $table->integer('discount')->nullable();
            $table->string('due_date')->nullable();
            $table->string('note')->nullable();
            $table->string('term')->nullable();
            $table->string('signature_name');
            $table->string('signature')->nullable();

            // --- Phần dành riêng cho đơn hàng bán (invoice_type = 1) ---
            $table->string('order_code')->nullable()->unique();
            // pending | confirmed | packing | shipping | completed | cancelled | returned
            $table->string('order_status')->nullable();
            $table->string('shipping_name')->nullable();
            $table->string('shipping_phone')->nullable();
            $table->string('shipping_address')->nullable();
            $table->bigInteger('shipping_fee')->default(0);
            $table->string('payment_method')->nullable(); // cod | banking | momo ...

            $table->timestamps();
            $table->softDeletes();

            $table->index(['invoice_type', 'order_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
