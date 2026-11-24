<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_participants', function (Blueprint $table) {
            $table->timestamp('checkin_at')->nullable()->after('produtos');
        });
    }

    public function down(): void
    {
        Schema::table('order_participants', function (Blueprint $table) {
            $table->dropColumn('checkin_at');
        });
    }
};