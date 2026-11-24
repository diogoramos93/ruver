<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'nome',
        'codigo',
        'tipo_desconto',
        'valor_desconto',
        'data_inicio',
        'data_termino',
        'quantidade_total',
        'quantidade_usada',
        'ativo',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // Função de ajuda para exibir o valor formatado na tela
    public function getValorFormatadoAttribute()
    {
        if ($this->tipo_desconto === 'porcentagem') {
            return number_format($this->valor_desconto, 0, ',', '.') . '%';
        }
        return 'R$ ' . number_format($this->valor_desconto, 2, ',', '.');
    }
}