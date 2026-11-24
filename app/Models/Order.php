<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ticket_id',
        'quantidade',
        'valor_total',
        'status',
        'forma_pagamento', // <- ADICIONE ISSO
        'asaas_payment_id', // <- ADICIONE ISSO
        'pix_copia_cola',
        'bank_slip_url',
    ];

    // O pedido pertence a um usuário (cliente)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // O pedido pertence a um ingresso/lote
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

// Relacionamento: Um pedido tem vários participantes
    public function participants()
    {
        return $this->hasMany(OrderParticipant::class);
    }

}