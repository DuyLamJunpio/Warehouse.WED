<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Đơn của trang thanh toán bên web bán hàng.
 *
 * Bên kho, đơn hàng thật là bản ghi Invoice. Bản ghi này giữ thứ Invoice không
 * có: mã ngẫu nhiên trên URL trang thanh toán, mã QR, hạn chuyển khoản, trạng
 * thái PayOS. Chỉ web bán hàng đọc và ghi nó, qua API có bí mật dùng chung.
 *
 * Cả đơn nằm trong một cột JSON: bảng này không được truy vấn theo trường bên
 * trong, nên tách cột chỉ đổi lấy một migration cho mỗi trường mà trang thanh
 * toán thêm vào. Hai cột tách ra ngoài là hai cột dùng để tra: `ref` (khách mở
 * trang bằng nó) và `order_code` (webhook PayOS chỉ biết nó).
 */
class StorefrontOrder extends Model
{
    /**
     * Giữ đơn bao lâu rồi dọn.
     *
     * Trang thanh toán chỉ cần vài phút; quãng còn lại là để tra soát khi khách
     * gọi lên hỏi về một lần chuyển khoản cũ.
     */
    public const KEEP_DAYS = 60;

    protected $fillable = ['ref', 'order_code', 'payload'];

    protected $casts = ['payload' => 'array'];

    /**
     * Dọn đơn cũ.
     *
     * Gọi lúc tạo đơn mới chứ không đặt vào lịch chạy nền: bảng này chỉ lớn lên
     * khi có đơn mới, nên đó cũng là lúc duy nhất cần dọn.
     */
    public static function prune(): void
    {
        static::where('created_at', '<', now()->subDays(self::KEEP_DAYS))->delete();
    }
}
