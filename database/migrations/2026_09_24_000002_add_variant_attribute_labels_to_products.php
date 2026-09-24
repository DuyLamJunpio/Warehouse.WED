<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Add optional display labels without changing any existing product or stock data. */
    public function up(): void
    {
        if (Schema::hasColumn('products', 'variant_attribute_labels')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            $table->json('variant_attribute_labels')->nullable()->after('unit');
        });
    }

    /**
     * Intentionally retain label data during a rollback. The project policy
     * preserves business data and this nullable metadata is harmless to leave.
     */
    public function down(): void
    {
        // No destructive schema operation.
    }
};
