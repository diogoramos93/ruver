<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Painel Super Admin - {{ config('app.name', 'IngressosApp') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased min-h-screen flex flex-col">

    <!-- HEADER / BARRA DE NAVEGAÇÃO -->
    <nav class="bg-black shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <div class="flex items-center space-x-8">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2">
                    <img src="{{ asset('images/1-Ruver Logo.svg') }}" alt="Ruver" class="h-8 w-auto">
                    <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full tracking-wider">Super Admin</span>
                </a>
                <div class="hidden md:flex space-x-6">
                    <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-white border-b-2 border-white pb-1">Dashboard</a>
                    <a href="{{ route('admin.organizers.index') }}" class="text-sm font-semibold text-gray-400 hover:text-white transition pb-1">Organizadores</a>
                    <a href="{{ route('admin.events.index') }}" class="text-sm font-semibold text-gray-400 hover:text-white transition pb-1">Todos os Eventos</a>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-xs text-gray-400 font-mono">{{ auth()->user()->email }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-bold text-gray-300 hover:text-white bg-gray-900 px-3 py-1.5 rounded-xl border border-gray-800 transition">
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full space-y-8">

        <!-- CABEÇALHO DA PÁGINA -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Painel de Controle Financeiro</h1>
                <p class="text-sm text-gray-500 mt-1">Visão consolidada da plataforma, taxas arrecadadas e desempenho de vendas.</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.organizers.create') }}" class="bg-black hover:bg-gray-800 text-white font-bold text-xs px-5 py-3 rounded-xl transition shadow-sm">
                    + Novo Organizador
                </a>
                <a href="{{ route('admin.events.index') }}" class="bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs px-4 py-3 rounded-xl border border-gray-300 transition shadow-sm">
                    Ver Eventos Globais
                </a>
            </div>
        </div>

        <!-- FILTRO DE DATA -->
        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap gap-4 items-end bg-white p-5 rounded-2xl border border-gray-100 shadow-sm w-full lg:w-max">
            <div>
                <label class="block text-[10px] font-extrabold text-gray-500 uppercase tracking-wider mb-1">Data Início</label>
                <input type="date" name="start_date" value="{{ $dataInicio }}" class="block w-full border-gray-200 rounded-xl text-sm focus:ring-black focus:border-black">
            </div>
            <div>
                <label class="block text-[10px] font-extrabold text-gray-500 uppercase tracking-wider mb-1">Data Fim</label>
                <input type="date" name="end_date" value="{{ $dataFimRaw }}" class="block w-full border-gray-200 rounded-xl text-sm focus:ring-black focus:border-black">
            </div>
            <button type="submit" class="bg-black hover:bg-gray-800 text-white font-bold text-sm px-6 py-2.5 rounded-xl transition shadow-sm h-[42px]">
                Filtrar Período
            </button>
            <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-gray-400 hover:text-gray-700 transition px-2 pb-3">Limpar Filtro</a>
        </form>

        <!-- CARDS DE METRICAS (KPIS) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- 1. Faturamento Total (Volume Bruto) -->
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Faturamento Total</span>
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-xl text-lg">💰</span>
                </div>
                <div class="mt-4">
                    <div class="text-2xl font-extrabold text-gray-900 font-mono">
                        R$ {{ number_format($faturamentoTotalPlataforma, 2, ',', '.') }}
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Volume bruto transacionado</p>
                </div>
            </div>

            <!-- 2. Lucro Super Admin (Sua Receita de Taxas via Split) -->
            <div class="bg-emerald-50/50 border border-emerald-100 p-6 rounded-3xl shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Lucro Plataforma (Taxas)</span>
                    <span class="p-2 bg-emerald-100 text-emerald-700 rounded-xl text-lg">📈</span>
                </div>
                <div class="mt-4">
                    <div class="text-2xl font-extrabold text-emerald-700 font-mono">
                        R$ {{ number_format($lucroSuperAdminTotal, 2, ',', '.') }}
                    </div>
                    <p class="text-xs text-emerald-600/80 mt-1">Sua comissão líquida via split</p>
                </div>
            </div>

            <!-- 3. Vendas & Eventos -->
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Ingressos Vendidos</span>
                    <span class="p-2 bg-purple-50 text-purple-600 rounded-xl text-lg">🎟️</span>
                </div>
                <div class="mt-4">
                    <div class="text-2xl font-extrabold text-gray-900 font-mono">
                        {{ number_format($totalIngressosVendidos, 0, ',', '.') }}
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Em {{ $totalEventos }} evento(s) ({{ $eventosAtivos }} ativo(s))
                    </p>
                </div>
            </div>

        </div>

        <!-- GRÁFICO E TABELA ORGANIZADORES -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- GRÁFICO DE VENDAS -->
            <div class="lg:col-span-2 bg-white border border-gray-100 rounded-3xl shadow-sm p-6 sm:p-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Volume de Vendas (Período Selecionado)</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Total de inscrições confirmadas por dia</p>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- ÚLTIMOS ORGANIZADORES -->
            <div class="bg-white border border-gray-100 rounded-3xl shadow-sm p-6 sm:p-8 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-3">
                        <h3 class="text-base font-bold text-gray-900">Organizadores Recentes</h3>
                        <a href="{{ route('admin.organizers.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">Ver Todos &rarr;</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($ultimosOrganizadores as $org)
                            <div class="py-3 flex justify-between items-center">
                                <div>
                                    <div class="text-sm font-bold text-gray-900">{{ $org->nome }}</div>
                                    <div class="text-xs text-gray-500">{{ $org->email }}</div>
                                </div>
                                <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-full {{ $org->tipo_taxa_plataforma === 'porcentagem' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-100' }}">
                                    {{ $org->tipo_taxa_plataforma === 'porcentagem' ? $org->taxa_plataforma.'%' : 'R$ '.number_format($org->taxa_plataforma, 2, ',', '.') }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 py-4">Nenhum organizador cadastrado.</p>
                        @endforelse
                    </div>
                </div>
                <div class="pt-4 border-t border-gray-100 mt-4">
                    <a href="{{ route('admin.organizers.create') }}" class="block text-center w-full bg-gray-50 hover:bg-gray-100 text-gray-800 text-xs font-bold py-2.5 rounded-xl border border-gray-200 transition">
                        + Cadastrar Novo Organizador
                    </a>
                </div>
            </div>

        </div>

        <!-- ÚLTIMOS EVENTOS -->
        <div class="bg-white border border-gray-100 rounded-3xl shadow-sm p-6 sm:p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Eventos Recentes na Plataforma</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Acompanhe os últimos eventos cadastrados</p>
                </div>
                <a href="{{ route('admin.events.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">Ver Todos os Eventos &rarr;</a>
            </div>

            <div class="overflow-x-auto border border-gray-200 rounded-2xl">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead class="bg-gray-50 text-gray-500 font-bold uppercase">
                        <tr>
                            <th class="px-6 py-4">Evento</th>
                            <th class="px-6 py-4">Organizador</th>
                            <th class="px-6 py-4">Cidade / UF</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700 bg-white">
                        @forelse($ultimosEventos as $event)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-6 py-4 font-bold text-gray-900">{{ $event->titulo }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $event->organizer ? $event->organizer->nome : 'N/A' }}</td>
                                <td class="px-6 py-4">{{ $event->cidade }}/{{ $event->estado }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase {{ $event->status === 'ativo' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $event->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('event.show', $event->slug) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-bold">Ver Site ↗</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-gray-400">Nenhum evento encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <footer class="bg-black text-gray-400 py-6 mt-12 border-t border-gray-900 text-xs text-center">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'IngressosApp') }} — Painel do Super Admin</p>
    </footer>

    <!-- SCRIPT CHART.JS -->
    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        const labels = {!! json_encode($vendasGrafico->pluck('data')) !!};
        const dataValues = {!! json_encode($vendasGrafico->pluck('total')) !!};

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['Hoje'],
                datasets: [{
                    label: 'Inscrições Vendidas',
                    data: dataValues.length ? dataValues : [0],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: '#10b981',
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
                    x: {
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { color: '#6b7280' }
                    },
                    y: {
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { color: '#6b7280', precision: 0 }
                    }
                }
            }
        });
    </script>

</body>
</html>