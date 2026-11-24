<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FormAnswer extends Model
{
    use HasFactory;

    /**
     * Tabela utilizada pelo Model.
     */
    protected $table = 'form_answers';

    /**
     * Campos permitidos para preenchimento.
     */
    protected $fillable = [
        'order_participant_id',
        'form_id',
        'form_field_id',
        'pergunta',
        'tipo',
        'resposta',
        'configuracoes_snapshot',
    ];

    /**
     * Conversão automática dos campos JSON.
     */
    protected $casts = [
        'resposta' => 'array',
        'configuracoes_snapshot' => 'array',
    ];

    /**
     * Participante que forneceu a resposta.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(
            OrderParticipant::class,
            'order_participant_id'
        );
    }

    /**
     * Formulário ao qual a resposta pertence.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(
            Form::class,
            'form_id'
        );
    }

    /**
     * Pergunta original do formulário.
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(
            FormField::class,
            'form_field_id'
        );
    }

    /**
     * Filtra respostas de um participante.
     */
    public function scopeForParticipant(
        Builder $query,
        int $participantId
    ): Builder {
        return $query->where(
            'order_participant_id',
            $participantId
        );
    }

    /**
     * Filtra respostas de um formulário.
     */
    public function scopeForForm(
        Builder $query,
        int $formId
    ): Builder {
        return $query->where('form_id', $formId);
    }
}
