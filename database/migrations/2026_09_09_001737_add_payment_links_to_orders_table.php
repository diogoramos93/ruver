<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('pix_copia_cola')->nullable()->after('asaas_payment_id');
            $table->string('bank_slip_url')->nullable()->after('pix_copia_cola');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['pix_copia_cola', 'bank_slip_url']);
        });
    }
};