<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organizer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // <--- ADICIONADO AQUI (Essencial para salvar a relação!)
        'nome',
        'cnpj',
        'email',
        'telefone',
        'slug',
        'ativo',
        // Perfil e Identidade Visual
        'foto', 
        'logo', 
        'icone', 
        'cor_marca', 
        'ano_criacao', 
        'bio', 
        'dominio',
        // Redes Sociais e Contato
        'facebook', 
        'instagram', 
        'youtube', 
        'whatsapp',
        // Integrações de Pagamento
        'mp_public_key', 
        'mp_access_token', 
        'asaas_api_key', 
        'asaas_wallet_id',
        'asaas_webhook_token',
        'tipo_taxa_plataforma', 
        'taxa_plataforma',      
    ];

    // 👇 CORRIGIDO: O organizador pertence a um usuário (através do user_id)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Um organizador tem muitos eventos
    public function events()
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    // Um organizador tem muitos produtos
    public function products()
    {
        return $this->hasMany(Product::class, 'organizer_id');
    }

    // Um organizador tem muitos formulários
    public function forms()
    {
        return $this->hasMany(Form::class, 'organizer_id');
    }

}