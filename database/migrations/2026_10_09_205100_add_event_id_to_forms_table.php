<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona o vínculo entre formulário e evento.
     */
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->foreignId('event_id')
                ->nullable()
                ->after('organizer_id')
                ->constrained('events')
                ->nullOnDelete();

            $table->index(['event_id', 'ativo']);
        });
    }

    /**
     * Desfaz a alteração, se necessário.
     */
    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'ativo']);
            $table->dropConstrainedForeignId('event_id');
        });
    }
};
