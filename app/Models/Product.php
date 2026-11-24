<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes; // 👈 Ativa os dois juntos aqui

    protected $fillable = [
        'organizer_id',
        'titulo',
        'categoria',
        'tipo',
        'preco_base',
        'foto',
        'ativo',
    ];

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }
}