<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class FavoritosController extends Controller
{
    // Mostra a tela "Meus Favoritos"
    public function index()
    {
        // Resgata o organizador injetado pelo Middleware na request do domínio atual
        $organizer = request()->get('currentOrganizer');

        // Busca os favoritos do usuário logado filtrando de forma segura
        $query = auth()->user()->favoritos();

        if ($organizer) {
            // Se estiver no domínio do organizador, filtra apenas os eventos que pertencem a ele
            $query->where('events.organizer_id', $organizer->id);
        }

        $favoritos = $query->paginate(10);
        
        return view('my-favorites', compact('favoritos'));
    }

    // Alterna o status (se já for favorito, ele tira. Se não for, ele adiciona)
    public function toggle(Event $event)
    {
        $user = auth()->user();
        
        // O método toggle do Laravel faz a mágica de ligar/desligar sozinho
        $user->favoritos()->toggle($event->id);

        return back()->with('success', 'Lista de favoritos atualizada!');
    }
}