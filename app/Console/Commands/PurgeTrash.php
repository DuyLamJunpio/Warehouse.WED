<?php

namespace App\Console\Commands;

use App\Services\TrashPurger;
use Illuminate\Console\Command;

class PurgeTrash extends Command
{
    protected $signature = 'trash:purge
        {--days=30 : Số ngày giữ bản ghi trong thùng rác}
        {--dry-run : Chỉ đếm, không xóa cứng}';

    protected $description = 'Xóa cứng dữ liệu bán hàng đã nằm trong thùng rác quá hạn';

    public function handle(TrashPurger $purger): int
    {
        $days = max(1, (int) $this->option('days'));
        $before = now()->subDays($days);
        $summary = $purger->purge($before, (bool) $this->option('dry-run'));
        $total = array_sum($summary);
        $prefix = $this->option('dry-run') ? '[dry-run] ' : '';

        foreach ($summary as $type => $count) {
            if ($count > 0) {
                $this->line($prefix . $type . ': ' . $count);
            }
        }

        $this->info($prefix . 'Tổng số bản ghi hết hạn: ' . $total);

        return self::SUCCESS;
    }
}
