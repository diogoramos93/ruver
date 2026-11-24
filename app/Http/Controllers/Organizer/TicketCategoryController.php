<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketCategory;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{
    // Exibe a tela com a lista de categorias do evento
    public function index(Event $event)
    {
        $organizer = \App\Models\Organizer::where('email', auth()->user()->email)->first();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            abort(403, 'Ação não autorizada.');
        }

        // Busca todas as categorias deste evento
        $categories = TicketCategory::where('event_id', $event->id)->get();

        return view('organizer.categories.index', compact('event', 'categories'));
    }

    // Salva uma nova categoria no banco
    public function store(Request $request, Event $event)
    {
        $organizer = \App\Models\Organizer::where('email', auth()->user()->email)->first();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            abort(403, 'Ação não autorizada.');
        }

        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        TicketCategory::create([
            'event_id' => $event->id,
            'nome' => $request->nome,
        ]);

        return redirect()->route('organizer.events.categories.index', $event->id)
            ->with('success', 'Categoria criada com sucesso!');
    }

    // Deleta uma categoria
    public function destroy(Event $event, TicketCategory $category)
    {
        $organizer = \App\Models\Organizer::where('email', auth()->user()->email)->first();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            abort(403, 'Ação não autorizada.');
        }

        $category->delete();

        return redirect()->route('organizer.events.categories.index', $event->id)
            ->with('success', 'Categoria excluída com sucesso!');
    }
}