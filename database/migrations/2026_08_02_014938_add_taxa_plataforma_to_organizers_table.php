<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizers', function (Blueprint $table) {
            $table->enum('tipo_taxa_plataforma', ['porcentagem', 'fixo'])->default('porcentagem')->after('ativo');
            $table->decimal('taxa_plataforma', 10, 2)->default(10.00)->after('tipo_taxa_plataforma'); // Padrão de 10%
        });
    }

    public function down(): void
    {
        Schema::table('organizers', function (Blueprint $table) {
            $table->dropColumn(['tipo_taxa_plataforma', 'taxa_plataforma']);
        });
    }
};