<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phần phí giao hàng do shop gánh.
 *
 * Tách khỏi shipping_fee vì hai con số này trả lời hai câu khác nhau: cột cũ là
 * tiền khách phải trả, cột này là tiền shop bỏ ra. Gộp chung thì hoặc là tính
 * nhầm vào hoá đơn của khách, hoặc là mất dấu khoản shop chịu khi tính lãi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->bigInteger('shop_shipping_fee')->default(0)->after('shipping_fee');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('shop_shipping_fee');
        });
    }
};
