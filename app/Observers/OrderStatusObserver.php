<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\OrderStatusMailer;

/**
 * Bắt mọi lần đơn hàng đổi trạng thái, để khách được báo bằng thư.
 *
 * Đặt ở observer chứ không nhét vào OrderController::updateStatus, vì trạng thái
 * đơn bị đổi ở nhiều đường và khách phải nhận thư ở tất cả:
 *
 *   • nhân viên bấm đổi trạng thái ngay tại danh sách đơn;
 *   • web bán hàng báo đã nhận tiền, đơn tự sang "đã xác nhận";
 *   • lệnh dọn đơn quá hạn tự huỷ đơn khách bỏ ngang.
 *
 * Thêm một đường thứ tư sau này thì cũng không phải nhớ gắn thư vào đó nữa.
 */
class OrderStatusObserver
{
    /**
     * Những bước chuyển không gửi thư.
     *
     * Đóng gói là việc nội bộ trong kho: khách không có gì để làm với tin đó, và
     * một lá thư kẹp giữa "đã xác nhận" và "đang giao" chỉ làm loãng những lá thư
     * có việc thật. Muốn tắt hay bật thêm bước nào thì sửa đúng dòng này.
     */
    private const SILENT_STATUSES = [Invoice::STATUS_PACKING];

    public function __construct(private OrderStatusMailer $mailer) {}

    public function updated(Invoice $invoice): void
    {
        // Phiếu nhập hàng từ nhà cung cấp cũng là invoice, nhưng không có khách.
        if ((int) $invoice->invoice_type !== Invoice::TYPE_ORDER) {
            return;
        }

        if (! $invoice->wasChanged('order_status')) {
            return;
        }

        $to = (string) $invoice->order_status;
        if ($to === '') {
            return;
        }

        if (in_array($to, self::SILENT_STATUSES, true)) {
            return;
        }

        /*
         * Đơn trả tiền ngay thì chỉ nhận một lá thư, là thư xác nhận thanh toán.
         *
         * Khách chuyển khoản xong, web bán hàng báo sang đây và đơn tự nhảy từ
         * "chờ xác nhận" sang "đã xác nhận" — cùng lúc web gửi thư "đã nhận thanh
         * toán". Hai lá thư về cùng một việc, cách nhau vài giây. Thư nói về tiền
         * là thư khách cần, nên bước "đã xác nhận" của đơn đã trả tiền im lặng.
         *
         * Xét theo trạng thái đã trả tiền chứ không theo đường gọi: nhân viên tự
         * bấm xác nhận một đơn đã chuyển khoản thì cũng là hai lá thư trùng nhau.
         * Các bước sau (đóng gói, đang giao, hoàn thành) vẫn có thư như thường.
         */
        if ($to === Invoice::STATUS_CONFIRMED && (int) $invoice->pay_status === 1) {
            return;
        }

        $from = $invoice->getOriginal('order_status');

        $this->mailer->queue($invoice, $from ? (string) $from : null, $to);
    }
}
