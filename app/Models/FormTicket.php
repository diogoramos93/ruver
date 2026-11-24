<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FormTicket extends Model
{
    use HasFactory;

    /**
     * Tabela de vinculação entre formulários e ingressos.
     */
    protected $table = 'form_ticket';

    /**
     * Campos permitidos para cadastro e edição.
     */
    protected $fillable = [
        'form_id',
        'ticket_id',
        'ordem',
        'ativo',
    ];

    /**
     * Conversão automática dos valores.
     */
    protected $casts = [
        'ordem' => 'integer',
        'ativo' => 'boolean',
    ];

    /**
     * Formulário vinculado.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(
            Form::class,
            'form_id'
        );
    }

    /**
     * Ingresso vinculado.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(
            Ticket::class,
            'ticket_id'
        );
    }

    /**
     * Apenas vinculações ativas.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    /**
     * Vinculações de um ingresso específico.
     */
    public function scopeForTicket(
        Builder $query,
        int $ticketId
    ): Builder {
        return $query->where('ticket_id', $ticketId);
    }

    /**
     * Vinculações de um formulário específico.
     */
    public function scopeForForm(
        Builder $query,
        int $formId
    ): Builder {
        return $query->where('form_id', $formId);
    }

    /**
     * Ordenação dos formulários vinculados.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('ordem')
            ->orderBy('id');
    }
}
