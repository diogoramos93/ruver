<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormField extends Model
{
    use HasFactory;

    protected $table = 'form_fields';

    /**
     * Campos permitidos para cadastro e edição.
     */
    protected $fillable = [
        'form_id',
        'titulo',
        'descricao',
        'tipo',
        'opcoes',
        'obrigatorio',
        'ativo',
        'ordem',
        'placeholder',
        'configuracoes',
    ];

    /**
     * Conversão automática dos dados.
     */
    protected $casts = [
        'opcoes' => 'array',
        'configuracoes' => 'array',
        'obrigatorio' => 'boolean',
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    /**
     * Tipos de perguntas disponíveis.
     */
    public const TIPOS = [
        'text' => 'Texto curto',
        'textarea' => 'Texto longo',
        'number' => 'Número',
        'email' => 'E-mail',
        'phone' => 'Telefone',
        'date' => 'Data',
        'select' => 'Lista suspensa',
        'radio' => 'Escolha única',
        'checkbox' => 'Múltipla escolha',
        'boolean' => 'Sim ou Não',
        'image' => 'Anexo de Imagem (até 2MB)',
    ];

    /**
     * Formulário ao qual a pergunta pertence.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    /**
     * Respostas associadas à pergunta.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(
            FormAnswer::class,
            'form_field_id'
        );
    }

    /**
     * Filtra somente perguntas ativas.
     */
    public function scopeActive($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Filtra perguntas por formulário.
     */
    public function scopeForForm($query, $formId)
    {
        return $query->where('form_id', $formId);
    }

    /**
     * Retorna perguntas na ordem configurada.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordem')
            ->orderBy('id');
    }

    /**
     * Identifica perguntas com opções.
     */
    public function hasOptions(): bool
    {
        return in_array($this->tipo, [
            'select',
            'radio',
            'checkbox',
        ], true);
    }
}
