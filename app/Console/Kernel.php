<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Không cần lịch nền: đơn quá hạn được thả ngay tại lúc có người cần hàng
        // (khách khác đặt / kiểm tồn) và lúc nhân viên mở trang Đơn hàng.
        // Vẫn chạy tay được: php artisan orders:cancel-expired
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
