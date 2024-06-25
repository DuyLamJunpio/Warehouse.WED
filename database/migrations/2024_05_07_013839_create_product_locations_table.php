<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_locations', function (Blueprint $table) {
            $table->id();
            $table->integer('zone');
            $table->string('shelf');
            $table->integer('level');
            $table->string('code');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->unique(['zone', 'shelf', 'level']); // Đảm bảo vị trí không trùng lặp
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_locations');
    }
};
