<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained()->onDelete('cascade');
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->string('categoria');
            $table->string('endereco');
            $table->string('estado');
            $table->string('cidade');
            $table->string('cep')->nullable();
            $table->text('descricao');
            $table->text('politica_reembolso');
            $table->dateTime('data_inicio');
            $table->dateTime('data_termino');
            $table->string('status')->default('ativo'); // ativo ou inativo
            $table->text('regulamentos');
            $table->json('documentos')->nullable(); // Para salvar até 5 PDFs
            $table->string('banner')->nullable(); // 1300x250
            $table->string('imagem_card')->nullable(); // 512x512
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};