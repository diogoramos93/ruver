<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',        
        'passaporte',   
        'role',
        'organizer_id', // (Se quiser pode deixar aqui por enquanto, mesmo sem usar)
        'foto',        
        'usuario',
        'telefone',
        'endereco',
        'pais',
        'cidade',
        'estado',
        'cep',
        'data_nascimento',
        'sexo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'data_nascimento' => 'date',
        ];
    }

    // 👇 CORRIGIDO: Um usuário TEM UM organizador (vinculado pelo user_id lá na tabela organizers)
    public function organizer()
    {
        return $this->hasOne(Organizer::class, 'user_id');
    }
    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

public function favoritos()
{
    // Um usuário pode favoritar vários eventos através da tabela 'favorites'
    return $this->belongsToMany(Event::class, 'favorites', 'user_id', 'event_id')->withTimestamps();
}

}