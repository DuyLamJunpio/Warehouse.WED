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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('customer_name');
            $table->string('customer_phone');
            // Web checkout nhận diện bằng số điện thoại; email và địa chỉ có
            // thể được bổ sung ở lần mua sau.
            $table->string('customer_email')->nullable();
            $table->string('address')->nullable();
            $table->string('avatar')->nullable();
            $table->integer('status')->default(0);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
