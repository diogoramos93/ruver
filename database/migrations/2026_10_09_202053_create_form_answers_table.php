
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cria a tabela de respostas dos formulários.
     */
    public function up(): void
    {
        Schema::create('form_answers', function (Blueprint $table) {
            $table->id();

            // Participante que respondeu
            $table->foreignId('order_participant_id')
                ->constrained('order_participants')
                ->cascadeOnDelete();

            // Formulário utilizado na inscrição
            // Nullable para preservar o histórico
            $table->foreignId('form_id')
                ->nullable()
                ->constrained('forms')
                ->nullOnDelete();

            // Pergunta respondida
            // Nullable para preservar o histórico
            $table->foreignId('form_field_id')
                ->nullable()
                ->constrained('form_fields')
                ->nullOnDelete();

            // Cópia do título original da pergunta
            $table->string('pergunta', 255);

            // Cópia do tipo original da pergunta
            $table->string('tipo', 50);

            // Resposta do participante
            // Suporta texto, número e múltiplas opções
            $table->json('resposta')->nullable();

            // Configuração original da pergunta
            // Preserva opções e outras configurações
            $table->json('configuracoes_snapshot')->nullable();

            // Datas de criação e atualização
            $table->timestamps();

            // Índices para consultas
            $table->index('order_participant_id');
            $table->index(['form_id', 'form_field_id']);
        });
    }

    /**
     * Remove a tabela em caso de rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_answers');
    }
};
