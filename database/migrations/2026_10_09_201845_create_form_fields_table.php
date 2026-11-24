
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cria as perguntas dos formulários dinâmicos.
     */
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();

            // Formulário ao qual a pergunta pertence
            $table->foreignId('form_id')
                ->constrained('forms')
                ->cascadeOnDelete();

            // Título da pergunta
            $table->string('titulo', 255);

            // Texto de ajuda opcional
            $table->text('descricao')->nullable();

            // Tipo da pergunta
            // Ex.: text, textarea, number, date, select,
            // radio, checkbox, email, phone, cpf, boolean
            $table->string('tipo', 50)->default('text');

            // Opções de resposta em JSON
            // Ex.: ["P", "M", "G", "GG"]
            $table->json('opcoes')->nullable();

            // Define se a resposta é obrigatória
            $table->boolean('obrigatorio')->default(false);

            // Define se a pergunta está ativa
            $table->boolean('ativo')->default(true);

            // Ordem de exibição das perguntas
            $table->unsignedInteger('ordem')->default(0);

            // Texto de exemplo dentro do campo
            $table->string('placeholder')->nullable();

            // Configurações adicionais
            // Ex.: limites, formatos e validações
            $table->json('configuracoes')->nullable();

            // Datas de criação e atualização
            $table->timestamps();

            // Índice para carregar perguntas ordenadas
            $table->index(['form_id', 'ativo', 'ordem']);
        });
    }

    /**
     * Remove a tabela em caso de rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
