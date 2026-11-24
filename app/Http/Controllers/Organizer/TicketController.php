<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Product;
use App\Models\TicketCategory; // 👇 Importamos a nova tabela aqui!
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Event $event)
    {
        $organizer = auth()->user()->organizer ?? \App\Models\Organizer::where('user_id', auth()->id())->orWhere('email', auth()->user()->email)->first();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            abort(403, 'Ação não autorizada.');
        }

        $tickets = $event->tickets()->get();

        return view('organizer.tickets.index', compact('event', 'tickets'));
    }

    public function create(Event $event)
    {
        $organizer = auth()->user()->organizer ?? \App\Models\Organizer::where('user_id', auth()->id())->orWhere('email', auth()->user()->email)->first();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            abort(403, 'Ação não autorizada.');
        }

        $produtos = Product::where('organizer_id', $organizer->id)->where('ativo', 1)->get();
        
        // 👇 AGORA BUSCA DIRETO DA TABELA NOVA DE CATEGORIAS 👇
        $modalidades = TicketCategory::where('event_id', $event->id)->pluck('nome');

        return view('organizer.tickets.create', compact('event', 'produtos', 'modalidades'));
    }

    public function store(Request $request, Event $event)
    {
        $organizer = auth()->user()->organizer ?? \App\Models\Organizer::where('user_id', auth()->id())->orWhere('email', auth()->user()->email)->first();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            abort(403, 'Ação não autorizada.');
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'modalidade' => 'required|string|max:255',
            'preco' => 'required|numeric|min:10',
            'quantidade_total' => 'required|integer|min:0',
            'taxa_organizador' => 'required|string',
            'data_inicio_vendas' => 'nullable|date',
            'data_fim_vendas' => 'nullable|date|after_or_equal:data_inicio_vendas',
            'idade_minima' => 'nullable|integer|min:0',
            'idade_maxima' => 'nullable|integer|min:0',
            'descricao' => 'nullable|string',
            'produtos' => 'nullable|array',
            'aceita_desconto_idoso' => 'nullable|boolean', // 👈 Nova regra de validação
        ]);

        Ticket::create([
            'event_id' => $event->id,
            'nome' => $request->nome,
            'modalidade' => $request->modalidade,
            'preco' => $request->preco,
            'quantidade_total' => $request->quantidade_total,
            'quantidade_disponivel' => $request->quantidade_total,
            'taxa_organizador' => $request->taxa_organizador,
            'data_inicio_vendas' => $request->data_inicio_vendas,
            'data_fim_vendas' => $request->data_fim_vendas,
            'idade_minima' => $request->idade_minima,
            'idade_maxima' => $request->idade_maxima,
            'produtos_vinculados' => $request->has('produtos') ? json_encode($request->produtos) : null,
            'descricao' => $request->descricao,
            'aceita_desconto_idoso' => $request->boolean('aceita_desconto_idoso'), // 👈 Salva a flag no banco
        ]);

        return redirect()->route('organizer.events.tickets.index', $event->id)->with('success', 'Ingresso cadastrado com sucesso!');
    }

    public function edit(Event $event, Ticket $ticket)
    {
        $organizer = auth()->user()->organizer ?? \App\Models\Organizer::where('user_id', auth()->id())->orWhere('email', auth()->user()->email)->first();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            abort(403, 'Ação não autorizada.');
        }

        $produtos = Product::where('organizer_id', $organizer->id)->where('ativo', 1)->get();
        
        // 👇 AGORA BUSCA DIRETO DA TABELA NOVA DE CATEGORIAS 👇
        $modalidades = TicketCategory::where('event_id', $event->id)->pluck('nome');

        return view('organizer.tickets.edit', compact('event', 'ticket', 'produtos', 'modalidades'));
    }

    public function update(Request $request, Event $event, Ticket $ticket)
    {
        $organizer = auth()->user()->organizer ?? \App\Models\Organizer::where('user_id', auth()->id())->orWhere('email', auth()->user()->email)->first();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            abort(403, 'Ação não autorizada.');
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'modalidade' => 'required|string|max:255',
            'preco' => 'required|numeric|min:10',
            'quantidade_total' => 'required|integer|min:0',
            'taxa_organizador' => 'required|string',
            'data_inicio_vendas' => 'nullable|date',
            'data_fim_vendas' => 'nullable|date|after_or_equal:data_inicio_vendas',
            'idade_minima' => 'nullable|integer|min:0',
            'idade_maxima' => 'nullable|integer|min:0',
            'descricao' => 'nullable|string',
            'produtos' => 'nullable|array',
            'aceita_desconto_idoso' => 'nullable|boolean', // 👈 Nova regra de validação
        ]);

        $diferenca = $request->quantidade_total - $ticket->quantidade_total;
        $novaQuantidadeDisponivel = $ticket->quantidade_disponivel + $diferenca;

        $ticket->update([
            'nome' => $request->nome,
            'modalidade' => $request->modalidade,
            'preco' => $request->preco,
            'quantidade_total' => $request->quantidade_total,
            'quantidade_disponivel' => $novaQuantidadeDisponivel < 0 ? 0 : $novaQuantidadeDisponivel,
            'taxa_organizador' => $request->taxa_organizador,
            'data_inicio_vendas' => $request->data_inicio_vendas,
            'data_fim_vendas' => $request->data_fim_vendas,
            'idade_minima' => $request->idade_minima,
            'idade_maxima' => $request->idade_maxima,
            'produtos_vinculados' => $request->has('produtos') ? json_encode($request->produtos) : null,
            'descricao' => $request->descricao,
            'aceita_desconto_idoso' => $request->boolean('aceita_desconto_idoso'), // 👈 Atualiza a flag no banco
        ]);

        return redirect()->route('organizer.events.tickets.index', $event->id)->with('success', 'Ingresso atualizado com sucesso!');
    }

    public function destroy(Event $event, Ticket $ticket)
    {
        $organizer = auth()->user()->organizer ?? \App\Models\Organizer::where('user_id', auth()->id())->orWhere('email', auth()->user()->email)->first();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            abort(403, 'Ação não autorizada.');
        }

        $temVendas = \App\Models\OrderParticipant::where('ticket_id', $ticket->id)->exists() 
            || \App\Models\Order::where('ticket_id', $ticket->id)->exists()
            || ($ticket->quantidade_disponivel < $ticket->quantidade_total);

        if ($temVendas) {
            return redirect()->route('organizer.events.tickets.index', $event->id)
                ->with('error', 'Não é possível excluir este ingresso pois ele já possui vendas realizadas.');
        }

        $ticket->delete();

        return redirect()->route('organizer.events.tickets.index', $event->id)
            ->with('success', 'Ingresso excluído com sucesso!');
    }
}