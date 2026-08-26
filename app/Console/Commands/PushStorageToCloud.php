<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Đẩy ảnh đang nằm trong storage/app lên kho ngoài (Supabase Storage).
 *
 * Giữ nguyên đường dẫn, ví dụ "public/images/abc.jpg" ở máy thành đúng
 * "public/images/abc.jpg" trên bucket - nhờ vậy các link đã lưu trong cơ sở
 * dữ liệu vẫn đúng, không phải cập nhật bảng nào.
 *
 * Chạy một lần trên máy còn giữ ảnh gốc:
 *   php artisan storage:push --disk=supabase
 */
class PushStorageToCloud extends Command
{
    protected $signature = 'storage:push
        {--disk=supabase : Tên disk đích khai trong config/filesystems.php}
        {--path=public : Thư mục gốc cần đẩy lên}
        {--force : Ghi đè cả tệp đã có trên kho ngoài}';

    protected $description = 'Đẩy ảnh trong storage/app lên kho lưu trữ ngoài';

    public function handle(): int
    {
        $source = Storage::disk('local');
        $targetName = $this->option('disk');
        $target = Storage::disk($targetName);

        // Laravel để sẵn .gitignore trong storage/app/public; nó không phải ảnh
        // và đẩy lên kho ngoài chỉ tổ rác bucket.
        $ignored = ['.gitignore', '.gitkeep', 'Thumbs.db', '.DS_Store'];

        $files = collect($source->allFiles($this->option('path')))
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
