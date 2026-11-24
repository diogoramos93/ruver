<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'nome',
    ];

    // Uma categoria pertence a um evento
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}