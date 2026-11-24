<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Vincula o produto ao organizador
            $table->foreignId('organizer_id')->constrained()->cascadeOnDelete();
            
            $table->string('titulo');
            $table->string('categoria')->nullable(); // Ex: Vestuário, Alimentação
            $table->enum('tipo', ['fisico', 'digital'])->default('fisico');
            
            // Preço base (se for tudo grátis ou se o valor ficar só no atributo, o padrão é 0)
            $table->decimal('preco_base', 10, 2)->default(0);
            
            $table->string('foto')->nullable();
            $table->boolean('ativo')->default(true);

            $table->softDeletes();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};