<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizers', function (Blueprint $table) {
            // Tiramos o ->unique() daqui porque o seu banco de dados já possui essa trava!
            // Vamos apenas garantir que ele aceite valores nulos.
            $table->string('dominio')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('organizers', function (Blueprint $table) {
            // Remove a restrição unique caso precise reverter
            $table->dropUnique('organizers_dominio_unique');
        });
    }
};