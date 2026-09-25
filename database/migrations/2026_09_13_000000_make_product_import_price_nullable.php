<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The project database is PostgreSQL. Use direct ALTER SQL because
        // changing a column through Schema::change() requires doctrine/dbal.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE products ALTER COLUMN import_price DROP NOT NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (DB::table('products')->whereNull('import_price')->exists()) {
            throw new RuntimeException(
                'Cannot make products.import_price required while NULL values exist. Fill those values before rolling back.'
            );
        }

        DB::statement('ALTER TABLE products ALTER COLUMN import_price SET NOT NULL');
    }
};
