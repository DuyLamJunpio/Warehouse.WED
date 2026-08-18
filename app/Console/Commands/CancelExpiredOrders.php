<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

/**
 * Huỷ những đơn từ web bán hàng đã quá hạn thanh toán mà chưa nhận được tiền.
 *
 * Kho bị trừ ngay lúc khách bấm thanh toán (để người sau không mua trùng hàng),
 * nên khách bỏ ngang mà đơn cứ nằm đó là hàng bị giữ vĩnh viễn. Hạn thanh toán
 * đặt theo services.storefront.payment_window_minutes (mặc định 15 phút).
 *
 * Ân hạn tồn tại vì hai lý do: webstore đẩy đơn sang đây TRƯỚC khi tạo link
 * PayOS (nên hạn hai bên lệch nhau vài giây), và webhook báo đã trả tiền có thể
 * tới muộn hơn thời điểm hết hạn một chút.
 */
class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired {--dry-run : Chỉ liệt kê, không đổi gì}';

    protected $description = 'Huỷ đơn web quá hạn thanh toán và hoàn hàng về kho';

    public function handle(): int
    {
        if ($this->option('dry-run')) {
            $grace = (int) config('services.storefront.expiry_grace_minutes', 5);

            $orders = Invoice::orders()
                ->where('order_status', Invoice::STATUS_PENDING)
                ->where('pay_status', 0)
                ->whereNotNull('payment_expires_at')
                ->where('payment_expires_at', '<', now()->subMinutes($grace))
                ->get(['order_code', 'payment_expires_at']);

            foreach ($orders as $order) {
                $this->line("  [dry-run] {$order->order_code} (hết hạn {$order->payment_expires_at})");
            }

            $this->info('Sẽ huỷ ' . $orders->count() . ' đơn quá hạn.');

            return self::SUCCESS;
        }

        $this->info('Đã huỷ ' . Invoice::cancelExpiredHolds() . ' đơn quá hạn.');

        return self::SUCCESS;
    }
}
