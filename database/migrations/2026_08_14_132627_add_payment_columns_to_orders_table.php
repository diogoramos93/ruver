<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('forma_pagamento')->nullable()->after('status');
            $table->string('asaas_payment_id')->nullable()->after('forma_pagamento');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['forma_pagamento', 'asaas_payment_id']);
        });
    }
};