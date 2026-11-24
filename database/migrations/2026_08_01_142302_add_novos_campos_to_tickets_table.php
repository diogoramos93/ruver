<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('modalidade')->nullable()->after('nome');
            $table->string('taxa_organizador')->default('repassar')->after('quantidade_disponivel');
            $table->dateTime('data_inicio_vendas')->nullable()->after('taxa_organizador');
            $table->dateTime('data_fim_vendas')->nullable()->after('data_inicio_vendas');
            $table->integer('idade_minima')->nullable()->after('data_fim_vendas');
            $table->integer('idade_maxima')->nullable()->after('idade_minima');
            $table->json('produtos_vinculados')->nullable()->after('idade_maxima');
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'modalidade', 
                'taxa_organizador', 
                'data_inicio_vendas', 
                'data_fim_vendas', 
                'idade_minima', 
                'idade_maxima', 
                'produtos_vinculados'
            ]);
        });
    }
};