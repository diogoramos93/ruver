<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('foto')->nullable();
            $table->string('usuario')->nullable()->unique();
            $table->string('telefone')->nullable();
            $table->string('endereco')->nullable();
            $table->string('pais')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado')->nullable();
            $table->string('cep')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['foto', 'usuario', 'telefone', 'endereco', 'pais', 'cidade', 'estado', 'cep']);
        });
    }
};