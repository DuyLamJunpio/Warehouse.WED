<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            // Mã DH cũ được giữ để nhận diện giao dịch đã quét QR trước thời
            // điểm đổi chuẩn, không dùng cho các đơn mới.
            $table->string('legacy_order_code', 32)->nullable()->unique()->after('order_code');
        });

        DB::table('invoices')
            ->whereNotNull('order_code')
            ->orderBy('id')
            ->chunkById(100, function ($invoices): void {
                foreach ($invoices as $invoice) {
                $oldCode = strtoupper((string) $invoice->order_code);
                if (! preg_match('/^DH[0-9]{6}[A-Z0-9]{4}$/', $oldCode)) {
                    continue;
                }

                // 8 chữ số sau RUNGU vừa đúng chuẩn SePay (3-10 chữ số), vừa
                // xác định duy nhất theo id nên migration có thể chạy an toàn.
                $number = (int) $invoice->id;
                do {
                    $newCode = 'RUNGU' . str_pad((string) $number, 8, '0', STR_PAD_LEFT);
                    $number++;
                } while (DB::table('invoices')
                    ->where('order_code', $newCode)
                    ->where('id', '!=', $invoice->id)
                    ->exists());

                DB::table('invoices')->where('id', $invoice->id)->update([
                    'legacy_order_code' => $oldCode,
                    'order_code' => $newCode,
                    'updated_at' => now(),
                ]);

                // Bảng đối soát cũng dùng mã mới để màn quản trị nhất quán.
                DB::table('sepay_transactions')
                    ->where('invoice_id', $invoice->id)
                    ->orWhere('order_code', $oldCode)
                    ->update([
                        'order_code' => $newCode,
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    // Không tự đổi ngược dữ liệu mã đơn: việc đó có thể làm mất khả năng đối
    // soát các giao dịch đã ghi nhận sau khi chuyển sang RUNGU.
    public function down(): void
    {
    }
};
