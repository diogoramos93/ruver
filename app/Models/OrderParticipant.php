<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderParticipant extends Model
{
    use HasFactory;

    /**
     * Campos permitidos para cadastro e edição.
     */
    protected $fillable = [
        'order_id',
        'ticket_id',
        'nome',
        'cpf',
        'passaporte',
        'data_nascimento',
        'sexo',
        'cidade',
        'equipe',
        'produtos',
        'checkin_at',
    ];

    /**
     * Conversão automática dos dados.
     */
    protected $casts = [
        'data_nascimento' => 'date',
        'produtos' => 'array',
    ];

    /**
     * Pedido ao qual o participante pertence.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Ingresso comprado pelo participante.
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Respostas dos formulários preenchidos
     * por este participante.
     */
    public function formAnswers(): HasMany
    {
        return $this->hasMany(
            FormAnswer::class,
            'order_participant_id'
        );
    }
}
