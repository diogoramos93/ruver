<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Se o usuário não estiver logado, manda para o login
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // Se o usuário tiver o papel correto, deixa passar
        if ($user->role === $role) {
            return $next($request);
        }

        // Se não tiver permissão, redireciona para a dashboard dele ou dá erro
        return redirect('/dashboard')->with('error', 'Acesso não autorizado.');
    }
}