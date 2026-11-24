
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cria a vinculação entre formulários e ingressos.
     */
    public function up(): void
    {
        Schema::create('form_ticket', function (Blueprint $table) {
            $table->id();

            // Formulário vinculado
            $table->foreignId('form_id')
                ->constrained('forms')
                ->cascadeOnDelete();

            // Ingresso vinculado
            $table->foreignId('ticket_id')
                ->constrained('tickets')
                ->cascadeOnDelete();

            // Ordem de exibição do formulário no ingresso
            $table->unsignedInteger('ordem')->default(0);

            // Permite ativar/desativar somente esta vinculação
            $table->boolean('ativo')->default(true);

            // Datas de criação e atualização
            $table->timestamps();

            // Impede vincular o mesmo formulário
            // duas vezes ao mesmo ingresso
            $table->unique(['form_id', 'ticket_id']);

            // Facilita a busca dos formulários de cada ingresso
            $table->index(['ticket_id', 'ativo', 'ordem']);
        });
    }

    /**
     * Remove a tabela em caso de rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_ticket');
    }
};
