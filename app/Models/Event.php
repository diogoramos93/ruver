<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'titulo',
        'slug',
        'categoria',
        'endereco',
        'estado',
        'cidade',
        'cep',
        'descricao',
        'politica_reembolso',
        'data_inicio',
        'data_termino',
        'status',
        'regulamentos',
        'documentos',
        'banner',
        'imagem_card',
        'aceita_pix',
        'aceita_cartao',
        'aceita_boleto',
        'data_limite_boleto',
        'banner_destaque_organizador', 
        'banner_destaque_global',      
    ];

    protected $casts = [
        'documentos' => 'array',
        'aceita_pix' => 'boolean',
        'aceita_cartao' => 'boolean',
        'aceita_boleto' => 'boolean',
        'data_limite_boleto' => 'date',
    ];

    // ========================================
    // NOVO: $appends — faz o accessor aparecer
    // automaticamente em JSON, collections, etc.
    // ========================================
    protected $appends = ['ja_passou'];

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // ========================================
    // NOVO: Accessor que verifica se o evento já
    // terminou (data_termino < hoje).
    //
    // Se quiser usar data_inicio em vez de
    // data_termino, troque a palavra
    // 'data_termino' por 'data_inicio' abaixo.
    // ========================================
    public function getJaPassouAttribute()
{
        if (!$this->data_termino) {
            return false;
        }

        return \Carbon\Carbon::parse($this->data_termino)->isPast();
    }
}