<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table): void {
            $table->json('content_blocks')->nullable()->after('subheading');
            $table->json('ctas')->nullable()->after('cta_link');
            $table->json('desktop_layout')->nullable()->after('ctas');
            $table->json('mobile_layout')->nullable()->after('desktop_layout');
            $table->string('mobile_media_type')->nullable()->after('mobile_path');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table): void {
            $table->dropColumn(['content_blocks', 'ctas', 'desktop_layout', 'mobile_layout', 'mobile_media_type']);
        });
    }
};
