<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Financeiro: {{ $organizer->nome }} - Super Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased min-h-screen flex flex-col">

    <!-- HEADER / NAV -->
    <nav class="bg-black shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <div class="flex items-center space-x-8">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2">
                    <img src="{{ asset('images/1-Ruver Logo.svg') }}" alt="Ruver" class="h-8 w-auto">
                    <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full tracking-wider">Super Admin</span>
                </a>
            </div>
            <a href="{{ route('admin.organizers.index') }}" class="text-xs font-bold text-gray-300 hover:text-white transition">
                &larr; Voltar para Organizadores
            </a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full space-y-8">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-gray-200 pb-6">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Detalhamento por Organizador</span>
                <h1 class="text-3xl font-extrabold text-gray-900 mt-1">{{ $organizer->nome }}</h1>
                <p class="text-xs text-gray-500 mt-1 font-mono">E-mail: {{ $organizer->email }} | CNPJ: {{ $organizer->cnpj ?: 'Não informado' }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="px-3 py-1 rounded-full text-xs font-mono font-bold border {{ $organizer->tipo_taxa_plataforma === 'porcentagem' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                    Taxa: {{ $organizer->tipo_taxa_plataforma === 'porcentagem' ? $organizer->taxa_plataforma.'%' : 'R$ '.number_format($organizer->taxa_plataforma, 2, ',', '.') }}
                </span>
                <a href="{{ route('admin.organizers.edit', $organizer->id) }}" class="bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs px-4 py-2.5 rounded-xl border border-gray-300 transition shadow-sm">
                    ✏️ Editar Taxa / Dados
                </a>
            </div>
        </div>

        <!-- METRICAS DO ORGANIZADOR -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Faturamento Bruto</span>
                <div class="text-2xl font-extrabold text-gray-900 font-mono mt-3">
                    R$ {{ number_format($faturamentoTotal, 2, ',', '.') }}
                </div>
                <p class="text-xs text-gray-500 mt-1">Total vendido pelo organizador</p>
            </div>

            <div class="bg-emerald-50/50 border border-emerald-100 p-6 rounded-3xl shadow-sm">
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Taxa Super Admin</span>
                <div class="text-2xl font-extrabold text-emerald-700 font-mono mt-3">
                    R$ {{ number_format($taxaSuperAdminTotal, 2, ',', '.') }}
                </div>
                <p class="text-xs text-emerald-600/80 mt-1">Lucro retido pela plataforma</p>
            </div>

            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Repasse ao Organizador</span>
                <div class="text-2xl font-extrabold text-gray-900 font-mono mt-3">
                    R$ {{ number_format($repasseOrganizador, 2, ',', '.') }}
                </div>
                <p class="text-xs text-gray-500 mt-1">Valor líquido do organizador</p>
            </div>

            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Inscrições Vendidas</span>
                <div class="text-2xl font-extrabold text-gray-900 font-mono mt-3">
                    {{ number_format($totalIngressosVendidos, 0, ',', '.') }}
                </div>
                <p class="text-xs text-gray-500 mt-1">Em {{ $events->count() }} evento(s)</p>
            </div>

        </div>

        <!-- TABELA DE EVENTOS DO ORGANIZADOR -->
        <div class="bg-white border border-gray-100 rounded-3xl shadow-xl p-6 sm:p-8 space-y-6">
            <h3 class="text-lg font-bold text-gray-900">Eventos deste Organizador</h3>

            <div class="overflow-x-auto border border-gray-200 rounded-2xl">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead class="bg-gray-50 text-gray-500 font-bold uppercase">
                        <tr>
                            <th class="px-6 py-4">Evento</th>
                            <th class="px-6 py-4">Cidade / UF</th>
                            <th class="px-6 py-4">Datas do Evento</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700 bg-white">
                        @forelse($events as $event)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-6 py-4 font-bold text-gray-900">{{ $event->titulo }}</td>
                                <td class="px-6 py-4">{{ $event->cidade }}/{{ $event->estado }}</td>
                                <td class="px-6 py-4">
                                    {{ \Carbon\Carbon::parse($event->data_inicio)->format('d/m/Y H:i') }}
                                </td>
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
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">Este organizador ainda não criou nenhum evento.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

</body>
</html>
