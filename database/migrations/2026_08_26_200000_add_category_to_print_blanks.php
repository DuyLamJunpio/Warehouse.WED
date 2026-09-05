<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Xếp phôi in vào danh mục — để web bán hàng lọc được khi phôi nhiều lên.
 *
 * DÙNG LẠI bảng `categories` chứ không dựng một bảng danh mục riêng cho phôi.
 * Lý do: "Áo thun", "Áo polo", "Hoodie" là cùng một thứ dù chiếc áo đó đang nằm
 * trên kệ hàng bán sẵn hay đang chờ in hình lên. Hai bảng song song nghĩa là chủ
 * shop khai "Áo thun" hai lần, rồi một hôm sửa tên ở một chỗ.
 *
 * Cột đặt tên `categories_id` cho khớp `products.categories_id` — tên hơi ngược
 * tai nhưng khớp với cái đang có quan trọng hơn.
 *
 * Để trống được, và đó là mặc định: phôi cũ chưa xếp danh mục vẫn bày bán bình
 * thường, chỉ là không rơi vào chip lọc nào. Ép khai ngay là chặn mọi lần lưu
 * phôi cho tới khi ai đó ngồi xếp lại hết.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_blanks', function (Blueprint $table) {
            // nullOnDelete: xoá hẳn một danh mục thì phôi rơi về "chưa xếp",
            // không kéo theo phôi. Danh mục dùng SoftDeletes nên đường xoá
            // thường ngày còn không chạm tới đây.
            $table->foreignId('categories_id')->nullable()->after('product_id')
                ->constrained('categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('print_blanks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('categories_id');
        });
    }
};
