<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->string('nome');
            $table->string('cpf')->nullable();
            $table->string('passaporte')->nullable();
            $table->date('data_nascimento');
            $table->string('sexo');
            $table->string('cidade');
            $table->string('equipe')->nullable();
            $table->json('produtos')->nullable(); // Para guardar os tamanhos de camiseta
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_participants');
    }
};