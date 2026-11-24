<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizers', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->index('organizer_id');
        });
    }

    public function down(): void
    {
        Schema::table('organizers', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['organizer_id']);
        });
    }
};
