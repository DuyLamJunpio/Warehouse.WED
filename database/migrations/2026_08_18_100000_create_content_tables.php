<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nội dung trang chủ chỉnh được từ trang quản trị.
 *
 * Trước đây slide hero, chữ chạy và tiêu đề các khối đều nằm cứng trong mã
 * nguồn web bán hàng, đổi một chữ cũng phải sửa code rồi build lại. Hai bảng
 * này để chủ shop tự đổi theo chương trình khuyến mãi hay dịp lễ tết.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            // Ảnh hoặc video nền của slide.
            $table->string('media_path');
            $table->string('media_type')->default('image'); // image | video
            // Khung hình tĩnh của video, để không giật lúc tải.
            $table->string('poster_path')->nullable();
            // Ảnh thay cho video trên điện thoại: video ngang 16:9 lên mobile bị
            // crop hai bên rất nhiều, và tải video tốn băng thông di động.
            $table->string('mobile_path')->nullable();
            $table->string('alt')->nullable();

            $table->string('heading')->nullable();
            $table->string('subheading')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_link')->nullable();

            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);

            // Hẹn giờ theo dịp. Để trống = hiện mãi.
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort_order']);
        });

        Schema::create('site_texts', function (Blueprint $table) {
            $table->id();

            // marquee = một dòng chữ chạy ngang; heading = tiêu đề một khối.
            $table->string('group')->default('marquee');
            // Với heading: khoá cố định như 'new_arrivals.title'. Với marquee: để trống.
            $table->string('key')->nullable();
            $table->text('value');

            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->timestamps();

            $table->index(['group', 'status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_texts');
        Schema::dropIfExists('banners');
    }
};
