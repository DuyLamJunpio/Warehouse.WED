<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Xóa mềm cho dữ liệu catalogue của các brand bán hàng.
 *
 * Module phôi/thiết kế in có vòng đời riêng và cố ý không nằm trong migration
 * này. Các index unique của biến thể, mẫu và media được đổi thành partial
 * unique index để một bản ghi đã vào thùng rác không chặn dữ liệu mới.
 */
return new class extends Migration
{
    private array $tables = [
        'image_models',
        'product_styles',
        'product_variants',
        'product_locations',
        'collections',
        'site_texts',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->softDeletes();
                });
            }
        }

        if (Schema::hasTable('image_models')) {
            $this->replaceUniqueIndex(
                'image_models_product_id_sha256_unique',
                'image_models_product_id_sha256_live_unique',
                'image_models',
                '(product_id, sha256)',
            );
        }

        if (Schema::hasTable('product_styles')) {
            $this->replaceUniqueIndex(
                'product_styles_product_id_name_key_unique',
                'product_styles_product_id_name_key_live_unique',
                'product_styles',
                '(product_id, name_key)',
            );
        }

        if (Schema::hasTable('product_variants')) {
            $this->replaceUniqueIndex(
                'product_variants_sku_unique',
                'product_variants_sku_live_unique',
                'product_variants',
                '(sku)',
            );
            $this->replaceUniqueIndex(
                'product_variants_product_style_id_size_color_unique',
                'product_variants_style_size_color_live_unique',
                'product_variants',
                '(product_style_id, size, color)',
            );
        }
    }

    public function down(): void
    {
        foreach ([
            'image_models_product_id_sha256_live_unique',
            'product_styles_product_id_name_key_live_unique',
            'product_variants_sku_live_unique',
            'product_variants_style_size_color_live_unique',
        ] as $index) {
            DB::statement('DROP INDEX IF EXISTS ' . $index);
        }

        if (Schema::hasTable('image_models')) {
            Schema::table('image_models', function (Blueprint $table): void {
                $table->unique(['product_id', 'sha256']);
            });
        }
        if (Schema::hasTable('product_styles')) {
            Schema::table('product_styles', function (Blueprint $table): void {
                $table->unique(['product_id', 'name_key']);
            });
        }
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->unique(['sku']);
                $table->unique(['product_style_id', 'size', 'color']);
            });
        }

        foreach (array_reverse($this->tables) as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropSoftDeletes();
                });
            }
        }
    }

    private function replaceUniqueIndex(
        string $oldIndex,
        string $newIndex,
        string $table,
        string $columns,
    ): void {
        $driver = Schema::getConnection()->getDriverName();

        // PostgreSQL tạo unique() dưới dạng constraint (kèm index), vì vậy
        // phải tháo constraint trước; DROP INDEX trực tiếp sẽ bị PostgreSQL
        // chặn vì index đang được constraint sử dụng.
        if ($driver === 'pgsql') {
            DB::statement(sprintf(
                'ALTER TABLE "%s" DROP CONSTRAINT IF EXISTS "%s"',
                $table,
                $oldIndex,
            ));
            DB::statement('DROP INDEX IF EXISTS "' . $oldIndex . '"');
        } elseif ($driver === 'mysql') {
            DB::statement(sprintf(
                'ALTER TABLE `%s` DROP INDEX `%s`',
                $table,
                $oldIndex,
            ));
        } else {
            DB::statement('DROP INDEX IF EXISTS ' . $oldIndex);
        }

        // PostgreSQL và SQLite hỗ trợ partial index. MySQL không có cú pháp
        // tương đương trong mọi phiên bản, nên vẫn giữ unique toàn bảng ở đó
        // để migration không hỏng; production của brand dùng PostgreSQL.
        if ($driver === 'mysql') {
            DB::statement(sprintf(
                'CREATE UNIQUE INDEX %s ON %s %s',
                $newIndex,
                $table,
                $columns,
            ));

            return;
        }

        DB::statement(sprintf(
            'CREATE UNIQUE INDEX IF NOT EXISTS %s ON %s %s WHERE deleted_at IS NULL',
            $newIndex,
            $table,
            $columns,
        ));
    }
};
