@extends('layouts.organizer')

@section('title', 'Financeiro Geral')

@section('content')

    <!-- Título da Página -->
    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Visão Geral Financeira</h2>
            <p class="text-sm text-gray-500 mt-1">Acompanhe o faturamento de todos os seus eventos ativos e encerrados.</p>
        </div>
        <!-- Botão opcional para exportar relatório no futuro -->
        <button class="bg-black hover:bg-gray-800 text-white px-5 py-2.5 text-sm font-bold rounded-xl shadow-sm transition">
            Exportar Relatório
        </button>
    </div>

    <!-- CARDS DE METRICAS GLOBAIS (KPIs) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Card 1: Receita Bruta Total -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Receita Bruta (Geral)</span>
                <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-gray-900 tracking-tight">R$ {{ number_format($receitaBrutaGlobal, 2, ',', '.') }}</span>
                <span class="block text-xs text-emerald-500 font-bold mt-1">+{{ $crescimentoMensal }}% neste mês</span>
            </div>
        </div>

        <!-- Card 2: Receita Líquida Total -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Receita Líquida (Geral)</span>
                <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-gray-900 tracking-tight">R$ {{ number_format($receitaLiquidaGlobal, 2, ',', '.') }}</span>
                <span class="block text-xs text-gray-500 mt-1">Taxas Descontadas (R$ {{ number_format($totalTaxasGlobal, 2, ',', '.') }})</span>
            </div>
        </div>

        <!-- Card 3: Total de Ingressos Vendidos -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Ingressos Vendidos</span>
                <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $totalIngressosVendidosGlobal }}</span>
                <span class="block text-xs text-gray-500 mt-1">Em {{ $totalPedidosGlobal }} pedidos aprovados</span>
            </div>
        </div>

        <!-- Card 4: Eventos Ativos -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Eventos Ativos</span>
                <div class="p-2.5 bg-amber-50 text-amber-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $eventosAtivos }}</span>
                <span class="block text-xs text-gray-500 mt-1">Gerando receita atualmente</span>
            </div>
        </div>
    </div>

    <!-- SESSÃO DE GRÁFICOS GLOBAIS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        <!-- Gráfico de Linha: Evolução Diária Global -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Evolução de Faturamento (Últimos 30 dias)</h3>
                    <p class="text-xs text-gray-500">Soma da receita de todos os seus eventos por dia</p>
                </div>
            </div>
            <div class="h-72">
                <canvas id="chartEvolucaoGlobal"></canvas>
            </div>
        </div>

        <!-- Gráfico Donut: Distribuição por Gênero Global -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 flex flex-col">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-900">Público Geral por Gênero</h3>
                <p class="text-xs text-gray-500">Base total de atletas em seus eventos</p>
            </div>
            <div class="flex-grow flex items-center justify-center h-64">
                <canvas id="chartGenerosGlobal"></canvas>
            </div>
            <div class="flex justify-around text-center pt-4 border-t border-gray-100 mt-2">
                <div>
                    <span class="block text-xs text-gray-400 font-medium">Masculino</span>
                    <span class="text-base font-bold text-indigo-600">{{ $generosGlobal['masculino'] }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-400 font-medium">Feminino</span>
                    <span class="text-base font-bold text-pink-500">{{ $generosGlobal['feminino'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- SEGUNDA SEÇÃO: Gráfico de Barras por Evento + Tabela de Eventos -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        <!-- Gráfico de Barras: Comparativo de Eventos -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-900">Arrecadação por Evento</h3>
                <p class="text-xs text-gray-500">Top eventos mais rentáveis</p>
            </div>
            <div class="h-72">
                <canvas id="chartEventos"></canvas>
            </div>
        </div>

        <!-- Tabela Detalhada Geral dos Eventos -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Desempenho por Evento</h3>
                    <p class="text-xs text-gray-500">Detalhamento individual de cada evento</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-gray-400 uppercase text-[11px] tracking-wider font-bold">
                        <tr>
                            <th class="py-3 px-4 rounded-l-xl">Evento</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Ingressos Vendidos</th>
                            <th class="py-3 px-4 text-right rounded-r-xl">Receita Gerada</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($detalhamentoEventos as $eventoInfo)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-4 px-4 font-bold text-gray-900">
                                    {{ $eventoInfo['titulo'] }}
                                </td>
                                <td class="py-4 px-4">
                                    @if($eventoInfo['status'] === 'publicado')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">Ativo</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Encerrado</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 font-medium text-gray-900">
                                    {{ $eventoInfo['ingressos_vendidos'] }} ingressos
                                </td>
                                <td class="py-4 px-4 text-right font-extrabold text-gray-900">
                                    R$ {{ number_format($eventoInfo['receita'], 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-400 text-sm">
                                    Você ainda não possui eventos com vendas registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SCRIPTS PARA RENDERIZAR OS GRÁFICOS CHART.JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // 1. Gráfico de Evolução Diária Global
            const vendasData = @json($vendasDiariasGlobais);
            const datasLabels = vendasData.map(item => item.data);
            const receitaValues = vendasData.map(item => parseFloat(item.receita));

            const ctxEvolucao = document.getElementById('chartEvolucaoGlobal').getContext('2d');
            new Chart(ctxEvolucao, {
                type: 'line',
                data: {
                    labels: datasLabels.length > 0 ? datasLabels : ['Sem vendas ainda'],
                    datasets: [{
                        label: 'Faturamento Global (R$)',
                        data: receitaValues.length > 0 ? receitaValues : [0],
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3,
                        pointBackgroundColor: '#10B981',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#F3F4F6' } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 2. Gráfico Donut de Gêneros Global
            const generosData = @json($generosGlobal);
            const ctxGeneros = document.getElementById('chartGenerosGlobal').getContext('2d');
            new Chart(ctxGeneros, {
                type: 'doughnut',
                data: {
                    labels: ['Masculino', 'Feminino', 'Outro'],
                    datasets: [{
                        data: [generosData.masculino, generosData.feminino, generosData.outro || 0],
                        backgroundColor: ['#6366F1', '#EC4899', '#9CA3AF'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { display: false } }
                }
            });

            // 3. Gráfico de Barras Comparativo de Eventos
            const eventosData = @json($detalhamentoEventos);
            const eventosLabels = eventosData.map(item => item.titulo);
            const eventosReceita = eventosData.map(item => item.receita);

            const ctxEventos = document.getElementById('chartEventos').getContext('2d');
            new Chart(ctxEventos, {
                type: 'bar',
                data: {
                    labels: eventosLabels.length > 0 ? eventosLabels : ['Nenhum evento'],
                    datasets: [{
                        label: 'Receita (R$)',
                        data: eventosReceita.length > 0 ? eventosReceita : [0],
                        backgroundColor: '#4F46E5',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#F3F4F6' } },
                        x: { grid: { display: false } }
                    }
                }
            });

        });
    </script>
@endsection