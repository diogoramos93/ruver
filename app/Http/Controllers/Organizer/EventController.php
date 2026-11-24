<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderParticipant;

class EventController extends Controller
{
    // Lista os eventos do organizador logado
    public function index()
    {
        $organizer = auth()->user()->organizer;
        $events = $organizer ? Event::where('organizer_id', $organizer->id)->get() : collect();

        return view('organizer.events.index', compact('events'));
    }

    // Formulário para criar novo evento
    public function create()
    {
        return view('organizer.events.create');
    }

    // Salvar o evento no banco
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'categoria' => 'required|string',
            'endereco' => 'required|string',
            'estado' => 'required|string|max:2',
            'cidade' => 'required|string',
            'descricao' => 'required|string',
            'politica_reembolso' => 'required|string',
            'data_inicio' => 'required|date',
            'data_termino' => 'required|date|after_or_equal:data_inicio',
            'status' => 'required|string',
            'regulamentos' => 'required|string',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'imagem_card' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'documentos.*' => 'nullable|mimes:pdf|max:5120',
            'aceita_pix' => 'nullable|boolean',
            'aceita_cartao' => 'nullable|boolean',
            'aceita_boleto' => 'nullable|boolean',
            'data_limite_boleto' => 'nullable|date',
        ]);

        $user = auth()->user();
        $organizer = $user->organizer;

        if (!$organizer) {
            $organizer = Organizer::create([
                'user_id' => $user->id,
                'nome' => $user->name,
                'slug' => Str::slug($user->name) . '-' . rand(100, 999),
            ]);
        }

        $bannerPath = null;
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('banners', 'public');
        }

        $cardPath = null;
        if ($request->hasFile('imagem_card')) {
            $cardPath = $request->file('imagem_card')->store('cards', 'public');
        }

        $documentosPaths = [];
        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $doc) {
                $nomeOriginal = $doc->getClientOriginalName();
                $documentosPaths[] = $doc->storeAs('documentos', $nomeOriginal, 'public');
            }
        }

        $evento = Event::create([
            'organizer_id' => $organizer->id,
            'titulo' => $request->titulo,
            'slug' => Str::slug($request->titulo) . '-' . rand(1000, 9999),
            'categoria' => $request->categoria,
            'endereco' => $request->endereco,
            'estado' => strtoupper($request->estado),
            'cidade' => $request->cidade,
            'cep' => $request->cep,
            'descricao' => $request->descricao,
            'politica_reembolso' => $request->politica_reembolso,
            'data_inicio' => $request->data_inicio,
            'data_termino' => $request->data_termino,
            'status' => $request->status,
            'regulamentos' => $request->regulamentos,
            'documentos' => $documentosPaths,
            'banner' => $bannerPath,
            'imagem_card' => $cardPath,
            'aceita_pix' => $request->boolean('aceita_pix'),
            'aceita_cartao' => $request->boolean('aceita_cartao'),
            'aceita_boleto' => $request->boolean('aceita_boleto'),
            'data_limite_boleto' => $request->input('data_limite_boleto'),
            
            // 👇 AQUI NO STORE (Você já tinha feito certo)
            'banner_destaque_organizador' => $request->boolean('banner_destaque_organizador'),
            'banner_destaque_global' => false,
        ]);

        $pastaWeclex = 'resultados_weclex/' . $evento->slug;
        Storage::disk('public')->makeDirectory($pastaWeclex);

        return redirect()->route('organizer.events.index')->with('success', 'Evento criado com sucesso e pasta de resultados gerada!');
    }

    public function edit($id)
    {
        $organizer = auth()->user()->organizer;
        $event = Event::where('organizer_id', $organizer->id)->findOrFail($id);

        return view('organizer.events.edit', compact('event'));
    }

    public function update(Request $request, $id)
    {
        $organizer = auth()->user()->organizer;
        $event = Event::where('organizer_id', $organizer->id)->findOrFail($id);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'categoria' => 'required|string',
            'endereco' => 'required|string',
            'estado' => 'required|string|max:2',
            'cidade' => 'required|string',
            'descricao' => 'required|string',
            'politica_reembolso' => 'required|string',
            'data_inicio' => 'required|date',
            'data_termino' => 'required|date|after_or_equal:data_inicio',
            'status' => 'required|string',
            'regulamentos' => 'required|string',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'imagem_card' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'documentos.*' => 'nullable|mimes:pdf|max:5120',
            'aceita_pix' => 'nullable|boolean',
            'aceita_cartao' => 'nullable|boolean',
            'aceita_boleto' => 'nullable|boolean',
            'data_limite_boleto' => 'nullable|date',
        ]);

        if ($request->hasFile('banner')) {
            if ($event->banner) {
                Storage::disk('public')->delete($event->banner);
            }
            $event->banner = $request->file('banner')->store('banners', 'public');
        }

        if ($request->hasFile('imagem_card')) {
            if ($event->imagem_card) {
                Storage::disk('public')->delete($event->imagem_card);
            }
            $event->imagem_card = $request->file('imagem_card')->store('cards', 'public');
        }

        $documentosSalvos = is_string($event->documentos) ? json_decode($event->documentos, true) : ($event->documentos ?? []);
        if (!is_array($documentosSalvos)) {
            $documentosSalvos = [];
        }

        if ($request->has('remover_documentos')) {
            $docsParaRemover = $request->input('remover_documentos');
            foreach ($docsParaRemover as $docRemover) {
                if (Storage::disk('public')->exists($docRemover)) {
                    Storage::disk('public')->delete($docRemover);
                }
                $documentosSalvos = array_filter($documentosSalvos, function($val) use ($docRemover) {
                    return $val !== $docRemover;
                });
            }
            $documentosSalvos = array_values($documentosSalvos);
        }

        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $doc) {
                $nomeOriginal = $doc->getClientOriginalName();
                $documentosSalvos[] = $doc->storeAs('documentos', $nomeOriginal, 'public');
            }
        }
        
        $event->documentos = $documentosSalvos;

        $event->update([
            'titulo' => $request->titulo,
            'categoria' => $request->categoria,
            'endereco' => $request->endereco,
            'estado' => strtoupper($request->estado),
            'cidade' => $request->cidade,
            'cep' => $request->cep,
            'descricao' => $request->descricao,
            'politica_reembolso' => $request->politica_reembolso,
            'data_inicio' => $request->data_inicio,
            'data_termino' => $request->data_termino,
            'status' => $request->status,
            'regulamentos' => $request->regulamentos,
            'banner' => $event->banner, 
            'imagem_card' => $event->imagem_card,
            'documentos' => $event->documentos,
            'aceita_pix' => $request->boolean('aceita_pix'),
            'aceita_cartao' => $request->boolean('aceita_cartao'),
            'aceita_boleto' => $request->boolean('aceita_boleto'),
            'data_limite_boleto' => $request->input('data_limite_boleto'),
            
            // 👇 AQUI NO UPDATE FALTAVA ISSO 👇
            'banner_destaque_organizador' => $request->boolean('banner_destaque_organizador'),
        ]);

        return redirect()->back()->with('success', 'Evento atualizado com sucesso!');
    }

    public function financial($id)
    {
        $organizer = auth()->user()->organizer;
        $event = Event::where('organizer_id', $organizer->id)->with('tickets')->findOrFail($id);

        $tickets = $event->tickets;
        $ticketIds = $tickets->pluck('id');

        $orders = Order::whereIn('ticket_id', $ticketIds)
            ->whereIn('status', ['pago', 'Pago', 'PAID', 'paid'])
            ->get();
            
        $participants = OrderParticipant::whereIn('order_id', $orders->pluck('id'))->get();

        $receitaBruta = $orders->sum('valor_total');
        $totalIngressosVendidos = $participants->count();
        $estoqueTotal = $tickets->sum('quantidade_total');
        $estoqueDisponivelTotal = $tickets->sum('quantidade_disponivel');
        $taxaOcupacao = $estoqueTotal > 0 ? round(($totalIngressosVendidos / $estoqueTotal) * 100, 1) : 0;
        $ticketMedio = $totalIngressosVendidos > 0 ? $receitaBruta / $totalIngressosVendidos : 0;
        $totalPedidos = $orders->count();

        $totalTaxaPlataforma = 0;
        if ($organizer) {
            if ($organizer->tipo_taxa_plataforma === 'fixo') {
                $totalTaxaPlataforma = $organizer->taxa_plataforma * $totalIngressosVendidos;
            } else {
                $totalTaxaPlataforma = $receitaBruta * (($organizer->taxa_plataforma ?? 0) / 100);
            }
        }
        $receitaLiquida = max(0, $receitaBruta - $totalTaxaPlataforma);

        $vendasDiarias = Order::whereIn('ticket_id', $ticketIds)
            ->whereIn('status', ['pago', 'Pago', 'PAID', 'paid'])
            ->select(
                DB::raw('DATE(created_at) as data'),
                DB::raw('SUM(valor_total) as receita'),
                DB::raw('SUM(quantidade) as ingressos')
            )
            ->groupBy('data')
            ->orderBy('data', 'asc')
            ->get();

        $detalhamentoLotes = $tickets->map(function ($ticket) use ($participants, $orders) {
            $qtdVendida = $participants->where('ticket_id', $ticket->id)->count();
            $receitaLote = $orders->where('ticket_id', $ticket->id)->sum('valor_total');
            
            return [
                'id' => $ticket->id,
                'nome' => $ticket->nome,
                'preco' => $ticket->preco,
                'quantidade_total' => $ticket->quantidade_total,
                'quantidade_disponivel' => $ticket->quantidade_disponivel,
                'vendidos' => $qtdVendida,
                'receita' => $receitaLote,
                'percentual_vendido' => $ticket->quantidade_total > 0 ? round(($qtdVendida / $ticket->quantidade_total) * 100, 1) : 0
            ];
        });

        $generos = [
            'masculino' => $participants->whereIn('sexo', ['M', 'Masculino', 'masculino'])->count(),
            'feminino' => $participants->whereIn('sexo', ['F', 'Feminino', 'feminino'])->count(),
            'outro' => $participants->whereNotIn('sexo', ['M', 'Masculino', 'masculino', 'F', 'Feminino', 'feminino'])->count(),
        ];

        return view('organizer.events.financial', compact(
            'event',
            'receitaBruta',
            'receitaLiquida',
            'totalTaxaPlataforma',
            'totalIngressosVendidos',
            'estoqueTotal',
            'estoqueDisponivelTotal',
            'taxaOcupacao',
            'ticketMedio',
            'totalPedidos',
            'vendasDiarias',
            'detalhamentoLotes',
            'generos'
        ));
    }
}