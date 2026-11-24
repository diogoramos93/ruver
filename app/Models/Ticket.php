<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    /**
     * Campos permitidos para cadastro e edição.
     */
    protected $fillable = [
        'event_id',
        'nome',
        'modalidade',
        'preco',
        'quantidade_total',
        'quantidade_disponivel',
        'taxa_organizador',
        'data_inicio_vendas',
        'data_fim_vendas',
        'idade_minima',
        'idade_maxima',
        'produtos_vinculados',
        'descricao',
        'aceita_desconto_idoso',
    ];

    /**
     * Um ingresso pertence a um evento.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Todos os formulários vinculados ao ingresso.
     */
    public function forms(): BelongsToMany
    {
        return $this->belongsToMany(
            Form::class,
            'form_ticket',
            'ticket_id',
            'form_id'
        )
        ->withPivot(['id', 'ordem', 'ativo'])
        ->withTimestamps()
        ->orderByPivot('ordem');
    }

    /**
     * Somente formulários ativos e com vínculo ativo.
     *
     * Este relacionamento será utilizado no checkout.
     */
    public function activeForms(): BelongsToMany
    {
        return $this->belongsToMany(
            Form::class,
            'form_ticket',
            'ticket_id',
            'form_id'
        )
        ->withPivot(['id', 'ordem', 'ativo'])
        ->withTimestamps()
        ->where('forms.ativo', true)
        ->wherePivot('ativo', true)
        ->orderByPivot('ordem');
    }

    /**
     * Registros de vinculação dos formulários.
     */
    public function formTickets(): HasMany
    {
        return $this->hasMany(
            FormTicket::class,
            'ticket_id'
        );
    }
}
