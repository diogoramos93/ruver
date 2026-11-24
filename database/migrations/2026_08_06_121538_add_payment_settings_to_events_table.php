<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('aceita_pix')->default(true);
            $table->boolean('aceita_cartao')->default(true);
            $table->boolean('aceita_boleto')->default(true);
            $table->date('data_limite_boleto')->nullable(); // Para o organizador definir quando o boleto some
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['aceita_pix', 'aceita_cartao', 'aceita_boleto', 'data_limite_boleto']);
        });
    }
};
