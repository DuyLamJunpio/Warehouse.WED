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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('categories_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('product_name');
            $table->string('slug')->unique();
            $table->string('barcode');
            $table->text('description')->nullable();
            $table->string('material')->nullable();   // chất liệu: cotton, kaki, lụa...
            $table->string('brand')->nullable();
            $table->string('unit')->default('cái');
            $table->bigInteger('import_price');
            $table->bigInteger('sell_price');
            $table->bigInteger('discount_price')->nullable(); // giá khuyến mãi, null = không giảm
            $table->boolean('is_featured')->default(false);   // đưa lên trang chủ
            $table->integer('status')->default(2);            // 1 = còn hàng, 2 = hết hàng, khác = ngưng bán
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
