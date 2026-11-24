<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\IdentifyTenant; // 👈 Importa o middleware aqui

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registrando o alias do nosso middleware de roles
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // 👇 Adiciona o IdentifyTenant na pilha web global para rodar em todas as páginas
        $middleware->web(append: [
            IdentifyTenant::class,
        ]);

        // 👇 LIBERANDO A ROTA DO ASAAS DO BLOQUEIO CSRF 👇
        $middleware->validateCsrfTokens(except: [
            'webhook/asaas',
            '/webhook/asaas',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();