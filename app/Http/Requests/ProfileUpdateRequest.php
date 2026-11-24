<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'usuario' => ['required', 'string', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'foto' => ['nullable', 'image', 'max:2048'], // máx 2MB
            
            // 👇 AQUI ESTÁ A MÁGICA: Protegendo o CPF e Passaporte 👇
            'cpf' => [
                'nullable', 
                'string', 
                'max:14', 
                Rule::unique(User::class)->ignore($this->user()->id)
            ],
            'passaporte' => [
                'nullable', 
                'string', 
                'max:30', 
                Rule::unique(User::class)->ignore($this->user()->id)
            ],
            // 👆 FIM DA PROTEÇÃO 👆

            'telefone' => ['nullable', 'string', 'max:20'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'pais' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:100'],
            'cep' => ['nullable', 'string', 'max:20'],
            'data_nascimento' => ['required', 'date'],
            'sexo' => ['required', 'string', 'in:Masculino,Feminino'],
        ];
    }

    /**
     * 👇 BÔNUS: Mensagens personalizadas para o usuário não ficar confuso 👇
     */
    public function messages(): array
    {
        return [
            'cpf.unique' => 'Este CPF já está sendo usado por outra conta. Se for o seu, entre em contato com o suporte.',
            'passaporte.unique' => 'Este Passaporte já está sendo usado por outra conta.',
            'usuario.unique' => 'Este nome de usuário já está em uso, escolha outro.',
        ];
    }
}