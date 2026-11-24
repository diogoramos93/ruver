<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('organizers', function (Blueprint $table) {
            $table->string('asaas_webhook_token')->nullable()->after('asaas_wallet_id');
        });
    }

    public function down()
    {
        Schema::table('organizers', function (Blueprint $table) {
            $table->dropColumn('asaas_webhook_token');
        });
    }
};