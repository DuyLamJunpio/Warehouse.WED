<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm chiều "mẫu" cho biến thể mà không thay đổi id/tồn kho hiện có.
 *
 * Ảnh nằm ở product_styles và được mọi màu/size của mẫu tham chiếu chung.
 * image_models.sha256 giúp máy chủ nhận ra cùng một file được tải lên lại.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('image_models', function (Blueprint $table) {
            $table->string('sha256', 64)->nullable()->after('path');
            $table->unsignedBigInteger('byte_size')->nullable()->after('sha256');
            $table->unique(['product_id', 'sha256']);
        });

        Schema::create('product_styles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('image_model_id')->nullable()->constrained('image_models')->nullOnDelete();
            $table->string('name', 100);
            $table->string('name_key', 100);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'name_key']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->foreignId('product_style_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_styles')
                ->cascadeOnDelete();
        });

        // Mỗi sản phẩm cũ nhận đúng một mẫu mặc định. Chỉ UPDATE khóa ngoại trên
        // các dòng hiện hữu nên variant_id, SKU và tồn kho đều được giữ nguyên.
        DB::table('products')->select('id')->orderBy('id')->chunkById(100, function ($products) {
            foreach ($products as $product) {
                $imageId = DB::table('image_models')
                    ->where('product_id', $product->id)
                    ->orderByDesc('is_pined')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->value('id');

                $styleId = DB::table('product_styles')->insertGetId([
                    'product_id' => $product->id,
                    'image_model_id' => $imageId,
                    'name' => 'Mẫu mặc định',
                    'name_key' => 'mẫu mặc định',
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('product_variants')
                    ->where('product_id', $product->id)
                    ->whereNull('product_style_id')
                    ->update(['product_style_id' => $styleId]);
            }
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'size', 'color']);
            $table->unique(['product_style_id', 'size', 'color']);
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropUnique(['product_style_id', 'size', 'color']);
            $table->dropConstrainedForeignId('product_style_id');
            $table->unique(['product_id', 'size', 'color']);
        });

        Schema::dropIfExists('product_styles');

        Schema::table('image_models', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'sha256']);
            $table->dropColumn(['sha256', 'byte_size']);
        });
    }
};
