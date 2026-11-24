<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organizer;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Define as datas do filtro (Padrão: últimos 30 dias até hoje)
        $dataInicio = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $dataFimRaw = $request->input('end_date', Carbon::now()->toDateString());
        
        // Adiciona 23:59:59 para incluir todas as vendas do último dia selecionado
        $dataFim = $dataFimRaw . ' 23:59:59';

        $totalOrganizadores = Organizer::count();
        $totalEventos = Event::count();
        $eventosAtivos = Event::where('status', 'ativo')->count();

        // 2. Pega todos os participantes de pedidos pagos DENTRO DO PERÍODO
        $participantesPagos = OrderParticipant::whereHas('order', function($q) {
            $q->where('status', 'pago');
        })
        ->whereBetween('created_at', [$dataInicio, $dataFim])
        ->with(['ticket.event.organizer', 'order'])
        ->get();

        $faturamentoTotalPlataforma = 0;
        $lucroSuperAdminTotal = 0;
        $totalIngressosVendidos = $participantesPagos->count();

        foreach ($participantesPagos as $p) {
            $preco = $p->ticket ? (float)$p->ticket->preco : 0;
            $faturamentoTotalPlataforma += $preco;

            $organizer = $p->ticket && $p->ticket->event ? $p->ticket->event->organizer : null;
            if ($organizer) {
                if ($organizer->tipo_taxa_plataforma === 'porcentagem') {
                    $taxa = $preco * ((float)($organizer->taxa_plataforma ?? 10) / 100);
                } else {
                    $taxa = (float)($organizer->taxa_plataforma ?? 5);
                }
                $lucroSuperAdminTotal += $taxa;
            } else {
                // Taxa padrão de 10% se não houver organizador vinculado
                $lucroSuperAdminTotal += ($preco * 0.10);
            }
        }

        // 3. Vendas para o Gráfico (Agora obedece ao filtro de data!)
        $vendasGrafico = OrderParticipant::select(
            DB::raw('DATE(created_at) as data'),
            DB::raw('count(*) as total')
        )
        ->whereHas('order', function($q) { $q->where('status', 'pago'); })
        ->whereBetween('created_at', [$dataInicio, $dataFim])
        ->groupBy('data')
        ->orderBy('data', 'ASC')
        ->get();

        // Últimos organizadores e eventos (Mantemos geral, sem filtro de data)
        $ultimosOrganizadores = Organizer::latest()->take(5)->get();
        $ultimosEventos = Event::with('organizer')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalOrganizadores',
            'totalEventos',
            'eventosAtivos',
            'faturamentoTotalPlataforma',
            'lucroSuperAdminTotal',
            'totalIngressosVendidos',
            'vendasGrafico',
            'ultimosOrganizadores',
            'ultimosEventos',
            'dataInicio',
            'dataFimRaw'
        ));
    }
}