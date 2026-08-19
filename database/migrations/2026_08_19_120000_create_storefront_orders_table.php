<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Đơn của trang thanh toán bên web bán hàng.
 *
 * Bảng invoices đã giữ đơn hàng thật rồi, bảng này giữ thứ nó không có: mã đơn
 * ngẫu nhiên trên URL trang thanh toán, mã QR, hạn chuyển khoản, và trạng thái
 * PayOS. Trước đây web bán hàng lưu chúng vào một tệp JSON trong thư mục tạm
 * của máy chủ; trên Vercel máy ảo bị thay sau vài phút là tệp mất trắng, khách
 * đang quét mã QR thì trang đơn hàng thành 404.
 *
 * Toàn bộ đơn nằm trong một cột JSON thay vì tách cột: chỉ web bán hàng đọc và
 * ghi nó, không màn hình quản trị nào truy vấn theo trường bên trong, nên tách
 * cột chỉ đổi lấy một migration cho mỗi lần trang thanh toán thêm một trường.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_orders', function (Blueprint $table) {
            $table->id();
            // Mã 12 ký tự trên URL /checkout/{ref} — đủ ngẫu nhiên để không dò được.
            $table->string('ref', 32)->unique();
            // Mã số nguyên PayOS khớp giao dịch theo. Webhook chỉ biết mã này.
            $table->bigInteger('order_code')->unique();
            $table->json('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_orders');
    }
};
