@extends('layouts.organizer')

@section('title', 'Financeiro do Evento - ' . $event->titulo)

@section('content')

    <!-- Título da Página -->
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Gerenciar Evento: {{ $event->titulo }}</h2>
    </div>

    <!-- PUXA O SUBMENU AUTOMÁTICO -->
    @include('organizer.partials.submenu')

    <!-- CARDS DE METRICAS (KPIs) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Card 1: Receita Bruta -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Receita Bruta Total</span>
                <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-gray-900 tracking-tight">R$ {{ number_format($receitaBruta, 2, ',', '.') }}</span>
                <span class="block text-xs text-gray-500 mt-1">{{ $totalPedidos }} pedido(s) realizado(s)</span>
            </div>
        </div>

        <!-- Card 2: Receita Líquida Estimada -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Receita Líquida (Est.)</span>
                <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-gray-900 tracking-tight">R$ {{ number_format($receitaLiquida, 2, ',', '.') }}</span>
                <span class="block text-xs text-gray-500 mt-1">Desc. Taxa Plataforma (R$ {{ number_format($totalTaxaPlataforma, 2, ',', '.') }})</span>
            </div>
        </div>

        <!-- Card 3: Ingressos Vendidos vs Estoque -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Vagas Preenchidas</span>
                <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $totalIngressosVendidos }} <span class="text-base text-gray-400 font-normal">/ {{ $estoqueTotal }}</span></span>
                
                <!-- Barra de progresso de lotação -->
                <div class="w-full bg-gray-100 rounded-full h-2 mt-3 overflow-hidden">
                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min(100, $taxaOcupacao) }}%"></div>
                </div>
                <span class="block text-xs text-indigo-600 font-bold mt-1 text-right">{{ $taxaOcupacao }}% ocupado</span>
            </div>
        </div>

        <!-- Card 4: Ticket Médio -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Ticket Médio</span>
                <div class="p-2.5 bg-amber-50 text-amber-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-gray-900 tracking-tight">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</span>
                <span class="block text-xs text-gray-500 mt-1">Valor médio por inscrição</span>
            </div>
        </div>
    </div>

    <!-- SESSÃO DE GRÁFICOS (Evolução Diária + Gênero) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        <!-- Gráfico de Linha: Evolução Diária (2 colunas) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Evolução do Faturamento Diário</h3>
                    <p class="text-xs text-gray-500">Histórico de receita gerada por dia de vendas</p>
                </div>
                <span class="text-xs font-bold text-gray-400 bg-gray-50 px-3 py-1 rounded-lg">R$ por Dia</span>
            </div>
            <div class="h-72">
                <canvas id="chartEvolucaoVendas"></canvas>
            </div>
        </div>

        <!-- Gráfico Donut: Distribuição por Gênero (1 coluna) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 flex flex-col">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-900">Público por Gênero</h3>
                <p class="text-xs text-gray-500">Distribuição dos atletas inscritos</p>
            </div>
            <div class="flex-grow flex items-center justify-center h-64">
                <canvas id="chartGeneros"></canvas>
            </div>
            <div class="flex justify-around text-center pt-4 border-t border-gray-100 mt-2">
                <div>
                    <span class="block text-xs text-gray-400 font-medium">Masculino</span>
                    <span class="text-base font-bold text-indigo-600">{{ $generos['masculino'] }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-400 font-medium">Feminino</span>
                    <span class="text-base font-bold text-pink-500">{{ $generos['feminino'] }}</span>
                </div>
                @if($generos['outro'] > 0)
                <div>
                    <span class="block text-xs text-gray-400 font-medium">Outro</span>
                    <span class="text-base font-bold text-gray-600">{{ $generos['outro'] }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- SEGUNDA SEÇÃO: Gráfico de Barras por Lote + Tabela Detalhada -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        <!-- Gráfico de Barras: Comparativo de Lotes (1 coluna) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-900">Arrecadação por Lote</h3>
                <p class="text-xs text-gray-500">Comparativo de receita entre modalidades</p>
            </div>
            <div class="h-72">
                <canvas id="chartLotes"></canvas>
            </div>
        </div>

        <!-- Tabela Detalhada dos Ingressos / Lotes (2 colunas) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Desempenho dos Ingressos</h3>
                    <p class="text-xs text-gray-500">Detalhamento individual de vendas e estoque</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-gray-400 uppercase text-[11px] tracking-wider font-bold">
                        <tr>
                            <th class="py-3 px-4 rounded-l-xl">Ingresso / Lote</th>
                            <th class="py-3 px-4">Preço Unit.</th>
                            <th class="py-3 px-4">Vendidos / Total</th>
                            <th class="py-3 px-4">Progresso</th>
                            <th class="py-3 px-4 text-right rounded-r-xl">Receita Gerada</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($detalhamentoLotes as $lote)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-4 px-4 font-bold text-gray-900">
                                    {{ $lote['nome'] }}
                                </td>
                                <td class="py-4 px-4 font-medium">
                                    R$ {{ number_format($lote['preco'], 2, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 font-medium">
                                    <span class="text-gray-900 font-bold">{{ $lote['vendidos'] }}</span> / {{ $lote['quantidade_total'] }}
                                </td>
                                <td class="py-4 px-4 w-36">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                            <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ min(100, $lote['percentual_vendido']) }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-gray-500">{{ $lote['percentual_vendido'] }}%</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-right font-extrabold text-gray-900">
                                    R$ {{ number_format($lote['receita'], 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-400 text-sm">
                                    Nenhum ingresso cadastrado para este evento.
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
            
            // 1. Configuração do Gráfico de Evolução Diária (Linha)
            const vendasData = @json($vendasDiarias);
            const datasLabels = vendasData.map(item => {
                const parts = item.data.split('-');
                return `${parts[2]}/${parts[1]}`;
            });
            const receitaValues = vendasData.map(item => parseFloat(item.receita));

            const ctxEvolucao = document.getElementById('chartEvolucaoVendas').getContext('2d');
            new Chart(ctxEvolucao, {
                type: 'line',
                data: {
                    labels: datasLabels.length > 0 ? datasLabels : ['Sem vendas ainda'],
                    datasets: [{
                        label: 'Faturamento (R$)',
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
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#F3F4F6' },
                            ticks: {
                                callback: function(value) { return 'R$ ' + value.toLocaleString('pt-BR'); }
                            }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 2. Configuração do Gráfico Donut de Gêneros
            const generosData = @json($generos);
            const ctxGeneros = document.getElementById('chartGeneros').getContext('2d');
            new Chart(ctxGeneros, {
                type: 'doughnut',
                data: {
                    labels: ['Masculino', 'Feminino', 'Outro'],
                    datasets: [{
                        data: [generosData.masculino, generosData.feminino, generosData.outro],
                        backgroundColor: ['#6366F1', '#EC4899', '#9CA3AF'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            // 3. Configuração do Gráfico de Barras por Lotes
            const lotesData = @json($detalhamentoLotes);
            const lotesLabels = lotesData.map(item => item.nome);
            const lotesReceita = lotesData.map(item => item.receita);

            const ctxLotes = document.getElementById('chartLotes').getContext('2d');
            new Chart(ctxLotes, {
                type: 'bar',
                data: {
                    labels: lotesLabels.length > 0 ? lotesLabels : ['Nenhum lote'],
                    datasets: [{
                        label: 'Receita (R$)',
                        data: lotesReceita.length > 0 ? lotesReceita : [0],
                        backgroundColor: '#4F46E5',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#F3F4F6' },
                            ticks: {
                                callback: function(value) { return 'R$ ' + value; }
                            }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });

        });
    </script>

@endsection