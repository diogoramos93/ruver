
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cria a tabela de formulários do Ruver.
     */
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();

            // Organizador proprietário do formulário
            $table->foreignId('organizer_id')
                ->constrained('organizers')
                ->cascadeOnDelete();

            // Identificação do formulário
            $table->string('nome', 150);

            // Descrição opcional
            $table->text('descricao')->nullable();

            // Controle de ativação
            $table->boolean('ativo')->default(true);

            // Controle de ordenação no painel
            $table->unsignedInteger('ordem')->default(0);

            // Datas de criação e atualização
            $table->timestamps();

            // Índices para consultas por organizador
            $table->index(['organizer_id', 'ativo']);
        });
    }

    /**
     * Remove a tabela em caso de rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
