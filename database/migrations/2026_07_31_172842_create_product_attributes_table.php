<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            // Vincula o atributo ao produto pai
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            
            $table->string('nome'); // Ex: 'P', 'M', 'Único'
            
            // Valor que será SOMADO ao preço base. Se não tiver acréscimo, é 0.
            $table->decimal('preco_adicional', 10, 2)->default(0);
            
            // Estoque específico para esse tamanho/variação
            $table->integer('estoque')->default(0);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
    }
};