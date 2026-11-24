<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizers', function (Blueprint $table) {
            // Perfil e Identidade Visual
            $table->string('foto')->nullable();
            $table->string('logo')->nullable();
            $table->string('icone')->nullable();
            $table->string('cor_marca')->nullable();
            $table->string('ano_criacao')->nullable(); // Data ou ano de criação da empresa
            $table->text('bio')->nullable();
            $table->string('dominio')->nullable(); // Domínio personalizado

            // Redes Sociais e Contato
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->string('whatsapp')->nullable();

            // Integrações de Pagamento (Mercado Pago e ASAAS)
            $table->string('mp_public_key')->nullable();
            $table->string('mp_access_token')->nullable();
            $table->string('asaas_api_key')->nullable();
            $table->string('asaas_wallet_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('organizers', function (Blueprint $table) {
            $table->dropColumn([
                'foto', 'logo', 'icone', 'cor_marca', 'ano_criacao', 'bio', 'dominio',
                'facebook', 'instagram', 'youtube', 'whatsapp',
                'mp_public_key', 'mp_access_token', 'asaas_api_key', 'asaas_wallet_id'
            ]);
        });
    }
};