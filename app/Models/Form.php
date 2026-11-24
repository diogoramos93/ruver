<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Form extends Model
{
    use HasFactory;

    protected $table = 'forms';

    protected $fillable = [
        'organizer_id',
        'event_id',
        'nome',
        'descricao',
        'ativo',
        'ordem',
    ];

    protected $casts = [
        'organizer_id' => 'integer',
        'event_id' => 'integer',
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    /**
     * Organizador proprietário do formulário.
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class, 'organizer_id');
    }

    /**
     * Evento ao qual o formulário pertence.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /**
     * Perguntas cadastradas no formulário.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class, 'form_id')
            ->orderBy('ordem')
            ->orderBy('id');
    }

    /**
     * Apenas perguntas ativas.
     */
    public function activeFields(): HasMany
    {
        return $this->hasMany(FormField::class, 'form_id')
            ->where('ativo', true)
            ->orderBy('ordem')
            ->orderBy('id');
    }

    /**
     * Ingressos vinculados ao formulário.
     */
    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(
            Ticket::class,
            'form_ticket',
            'form_id',
            'ticket_id'
        )
            ->withPivot(['id', 'ordem', 'ativo'])
            ->withTimestamps();
    }

    /**
     * Apenas formulários ativos.
     */
    public function scopeActive($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Filtra formulários por organizador.
     */
    public function scopeForOrganizer($query, $organizerId)
    {
        return $query->where('organizer_id', $organizerId);
    }

    /**
     * Filtra formulários por evento.
     */
    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }
}
