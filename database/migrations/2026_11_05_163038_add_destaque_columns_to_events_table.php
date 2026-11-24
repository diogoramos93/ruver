<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Adiciona a coluna para o destaque na página do organizador
            $table->boolean('banner_destaque_organizador')->default(false)->after('banner');
            
            // Adiciona a coluna para o destaque global na Ruver.app
            $table->boolean('banner_destaque_global')->default(false)->after('banner_destaque_organizador');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Remove as colunas caso você precise dar um rollback
            $table->dropColumn(['banner_destaque_organizador', 'banner_destaque_global']);
        });
    }
};