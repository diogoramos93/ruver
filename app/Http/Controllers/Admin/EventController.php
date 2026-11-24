<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\Order;
use App\Models\OrderParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    // Listagem global de eventos da plataforma com busca e filtro por organizador
    public function index(Request $request)
    {
        $query = Event::with('organizer', 'tickets');

        if ($request->filled('search')) {
            $busca = $request->search;
            $query->where('titulo', 'like', "%{$busca}%")
                  ->orWhere('cidade', 'like', "%{$busca}%");
        }

        if ($request->filled('organizer_id')) {
            $query->where('organizer_id', $request->organizer_id);
        }

        $events = $query->latest()->paginate(15)->withQueryString();
        $organizadores = Organizer::orderBy('nome')->get();

        return view('admin.events.index', compact('events', 'organizadores'));
    }

    // Exclusão Segura do Evento no Super Admin (Remoção limpa em cascata + remoção de imagens do storage)
    public function destroy(Event $event)
    {
        DB::transaction(function() use ($event) {
            $ticketIds = $event->tickets()->pluck('id');

            // 1. Apaga inscrições de participantes vinculadas aos ingressos do evento
            OrderParticipant::whereIn('ticket_id', $ticketIds)->delete();

            // 2. Apaga pedidos vinculados aos ingressos do evento
            Order::whereIn('ticket_id', $ticketIds)->delete();

            // 3. Apaga lotes/ingressos
            $event->tickets()->delete();

            // 4. Apaga categorias do evento
            \App\Models\TicketCategory::where('event_id', $event->id)->delete();

            // 5. Apaga cupons do evento
            \App\Models\Coupon::where('event_id', $event->id)->delete();

            // 6. Remove arquivos físicos (banner, card, documentos) do storage para não deixar lixo
            if ($event->banner && Storage::disk('public')->exists($event->banner)) {
                Storage::disk('public')->delete($event->banner);
            }
            if ($event->imagem_card && Storage::disk('public')->exists($event->imagem_card)) {
                Storage::disk('public')->delete($event->imagem_card);
            }
            if (is_array($event->documentos)) {
                foreach ($event->documentos as $doc) {
                    if (Storage::disk('public')->exists($doc)) {
                        Storage::disk('public')->delete($doc);
                    }
                }
            }

            // 7. Apaga o evento
            $event->delete();
        });

        return redirect()->route('admin.events.index')->with('success', "Evento '{$event->titulo}' e todos os registros associados foram excluídos com segurança!");
    }
}
