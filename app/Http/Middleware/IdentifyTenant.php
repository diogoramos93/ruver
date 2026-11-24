<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Organizer;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        // Pega o host atual (ex: ingressosdajoao.com.br ou localhost)
        $host = $request->getHost();

        // Remove a porta se estiver rodando em ambiente de desenvolvimento (ex: localhost:8000)
        $hostWithoutPort = explode(':', $host)[0];

        // Busca no banco se existe algum organizador com esse domínio cadastrado
        $organizer = Organizer::where('dominio', $hostWithoutPort)
                              ->orWhere('dominio', $host)
                              ->first();

        if ($organizer) {
            // Se encontrou, injeta o organizador na requisição para usarmos em qualquer lugar
            app()->instance('currentOrganizer', $organizer);
            $request->merge(['currentOrganizer' => $organizer]);
        }

        return $next($request);
    }
}