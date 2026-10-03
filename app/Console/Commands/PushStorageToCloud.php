<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sao chép media từ một disk sang disk khác, không xóa nguồn.
 *
 * Giữ nguyên object key, nhờ vậy các link/path đã lưu trong cơ sở dữ liệu
 * vẫn đúng, không phải cập nhật bảng nào.
 *
 * Chạy một lần trên máy còn giữ ảnh gốc:
 *   php artisan storage:push --source=local --disk=r2
 */
class PushStorageToCloud extends Command
{
    protected $signature = 'storage:push
        {--disk=r2 : Tên disk đích khai trong config/filesystems.php}
        {--source=local : Tên disk nguồn khai trong config/filesystems.php}
        {--path= : Chỉ đồng bộ prefix này; mặc định là toàn bộ disk}
        {--force : Ghi đè cả tệp đã có trên kho ngoài}';

    protected $description = 'Sao chép media giữa hai filesystem disk mà không xóa nguồn';

    public function handle(): int
    {
        $source = Storage::disk($this->option('source'));
        $targetName = $this->option('disk');
        $target = Storage::disk($targetName);

        // Laravel để sẵn .gitignore trong storage/app/public; nó không phải ảnh
        // và đẩy lên kho ngoài chỉ tổ rác bucket.
        $ignored = ['.gitignore', '.gitkeep', 'Thumbs.db', '.DS_Store'];

        $path = $this->option('path');
        $files = collect($path === null || $path === '' ? $source->allFiles() : $source->allFiles($path))
            ->reject(fn (string $file) => in_array(basename($file), $ignored, true))
            ->values()
            ->all();

        if ($files === []) {
            $this->warn('Không tìm thấy tệp nào để đẩy lên.');

            return self::SUCCESS;
        }

        $uploaded = 0;
        $skipped = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            $bar->advance();

            if (! $this->option('force') && $target->exists($file)) {
                $skipped++;

                continue;
            }

            $stream = $source->readStream($file);

            if ($stream === null) {
                $failed++;
                $this->newLine();
                $this->error("Không đọc được: {$file}");

                continue;
            }

            $ok = $target->writeStream($file, $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }

            if ($ok) {
                $uploaded++;

                continue;
            }

            $failed++;
            $this->newLine();
            $this->error("Đẩy lên thất bại: {$file}");
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Đã đẩy lên {$targetName}: {$uploaded} tệp, bỏ qua {$skipped} tệp đã có, lỗi {$failed} tệp.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
