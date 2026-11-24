<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            
            // Adicionado o 'unique:'.User::class para bloquear CPFs e Passaportes repetidos
            'cpf' => ['required_without:passaporte', 'nullable', 'string', 'max:14', 'unique:'.User::class],
            'passaporte' => ['required_without:cpf', 'nullable', 'string', 'max:30', 'unique:'.User::class],
            
            'data_nascimento' => ['required', 'date'],
            'telefone' => ['nullable', 'string', 'max:20'],
        ], [
            // Mensagens personalizadas de erro
            'cpf.required_without' => 'Você precisa informar o CPF ou o Passaporte.',
            'passaporte.required_without' => 'Você precisa informar o Passaporte ou o CPF.',
            'cpf.unique' => 'Este CPF já está cadastrado em nosso sistema.',
            'passaporte.unique' => 'Este Passaporte já está cadastrado em nosso sistema.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'cliente', // <-- Mantido do seu original
            'cpf' => $request->cpf,
            'passaporte' => $request->passaporte,
            'data_nascimento' => $request->data_nascimento,
            'telefone' => $request->telefone,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false)); // <-- Mantido do seu original
    }
}
