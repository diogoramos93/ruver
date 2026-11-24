<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderParticipant;
use App\Models\Organizer; // <-- Adicionado o model Organizer aqui!
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialGlobalController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role !== 'organizador') {
            abort(403, 'ERRO DE ACESSO: Esta área é exclusiva para Organizadores.');
        }

        // A MÁGICA REAL: Busca o organizador pelo EMAIL (igual o EventController faz!)
        $organizer = Organizer::where('email', $user->email)->first();

        if (!$organizer) {
            abort(403, 'Não encontramos um perfil de organizador vinculado ao seu e-mail.');
        }

        $organizerId = $organizer->id;

        // Agora sim! Busca os eventos usando o organizer_id correto (ID 1 no seu caso)
        $todosEventos = Event::where('organizer_id', $organizerId)->get();
                             
        $eventIds = $todosEventos->pluck('id');

        // Pega todos os pedidos PAGOS vinculados a esses eventos
        $pedidosPagos = Order::whereIn('status', ['pago', 'Pago', 'PAID', 'paid'])
            ->whereHas('ticket', function ($query) use ($eventIds) {
                $query->whereIn('event_id', $eventIds);
            })
            ->get();

        // --- CÁLCULOS GERAIS ---
        $receitaBrutaGlobal = $pedidosPagos->sum('valor_total');
        $totalPedidosGlobal = $pedidosPagos->count();
        $totalIngressosVendidosGlobal = $pedidosPagos->sum('quantidade');
        
        // Mantemos 'ativo' conforme vimos no seu banco
        $eventosAtivos = $todosEventos->where('status', 'ativo')->count();

        // --- CONFIGURAÇÃO DA TAXA DA PLATAFORMA ---
        // Agora podemos puxar a taxa real do organizador se existir!
        $tipoTaxa = $organizer->tipo_taxa_plataforma ?? 'porcentagem'; 
        $valorTaxa = $organizer->taxa_plataforma ?? 10; 

        $totalTaxasGlobal = 0;
        if ($tipoTaxa === 'porcentagem') {
            $totalTaxasGlobal = $receitaBrutaGlobal * ($valorTaxa / 100);
        } elseif ($tipoTaxa === 'fixo') {
            $totalTaxasGlobal = $totalIngressosVendidosGlobal * $valorTaxa;
        }
        
        $receitaLiquidaGlobal = $receitaBrutaGlobal - $totalTaxasGlobal;
        $crescimentoMensal = 0; 

        // --- GRÁFICOS GLOBAIS ---
        
        $participantesGlobais = OrderParticipant::whereHas('ticket', function ($query) use ($eventIds) {
            $query->whereIn('event_id', $eventIds);
        })->whereHas('order', function ($q) {
            $q->whereIn('status', ['pago', 'Pago', 'PAID', 'paid']);
        })->get();

        $generosGlobal = [
            'masculino' => $participantesGlobais->whereIn('sexo', ['M', 'Masculino', 'masculino'])->count(),
            'feminino' => $participantesGlobais->whereIn('sexo', ['F', 'Feminino', 'feminino'])->count(),
            'outro' => $participantesGlobais->whereNotIn('sexo', ['M', 'Masculino', 'masculino', 'F', 'Feminino', 'feminino'])->count(),
        ];

        $vendasDiariasGlobais = Order::select(
                DB::raw('DATE(created_at) as data'),
                DB::raw('SUM(valor_total) as receita')
            )
            ->whereIn('status', ['pago', 'Pago', 'PAID', 'paid'])
            ->whereHas('ticket', function ($query) use ($eventIds) {
                $query->whereIn('event_id', $eventIds);
            })
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('data')
            ->orderBy('data', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'data' => Carbon::parse($item->data)->format('d/m'),
                    'receita' => $item->receita
                ];
            });

        $detalhamentoEventos = [];

        foreach ($todosEventos as $evento) {
            $pedidosDoEvento = Order::whereIn('status', ['pago', 'Pago', 'PAID', 'paid'])
                ->whereHas('ticket', function ($query) use ($evento) {
                    $query->where('event_id', $evento->id);
                })->get();

            $receitaEvento = $pedidosDoEvento->sum('valor_total');
            $ingressosVendidos = $pedidosDoEvento->sum('quantidade');

            if ($ingressosVendidos > 0) {
                $detalhamentoEventos[] = [
                    'titulo' => $evento->titulo,
                    'status' => $evento->status ?? 'encerrado', 
                    'ingressos_vendidos' => $ingressosVendidos,
                    'receita' => $receitaEvento
                ];
            }
        }

        return view('organizer.financeiro.index', compact(
            'receitaBrutaGlobal', 'receitaLiquidaGlobal', 'totalTaxasGlobal',
            'totalIngressosVendidosGlobal', 'totalPedidosGlobal', 'eventosAtivos',
            'crescimentoMensal', 'generosGlobal', 'vendasDiariasGlobais', 'detalhamentoEventos'
        ));
    }
}