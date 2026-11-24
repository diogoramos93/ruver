<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('nome'); // Ex: Cupom Promocional de Inverno
            $table->string('codigo'); // Ex: INVERNO20
            $table->enum('tipo_desconto', ['porcentagem', 'fixo']); // Adaptado para 'fixo' e 'porcentagem'
            $table->decimal('valor_desconto', 10, 2); 
            $table->dateTime('data_inicio')->nullable(); // Novo
            $table->dateTime('data_termino')->nullable(); // Novo
            $table->integer('quantidade_total')->nullable(); // Limite de uso
            $table->integer('quantidade_usada')->default(0); 
            $table->boolean('ativo')->default(true); // Chavinha de Ativo/Inativo
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupons');
    }
};