<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gerenciar Organizadores - Super Admin</title>
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
                <div class="hidden md:flex space-x-6">
                    <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-gray-400 hover:text-white transition pb-1">Dashboard</a>
                    <a href="{{ route('admin.organizers.index') }}" class="text-sm font-bold text-white border-b-2 border-white pb-1">Organizadores</a>
                    <a href="{{ route('admin.events.index') }}" class="text-sm font-semibold text-gray-400 hover:text-white transition pb-1">Todos os Eventos</a>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.organizers.create') }}" class="bg-white hover:bg-gray-100 text-black font-extrabold text-xs px-4 py-2 rounded-xl transition shadow-sm">
                    + Novo Organizador
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full space-y-8">

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-bold shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Organizadores Cadastrados</h1>
                <p class="text-xs text-gray-500 mt-1">Gerencie contas, acessos e taxas da plataforma por organizador</p>
            </div>
            <form method="GET" action="{{ route('admin.organizers.index') }}" class="w-full sm:w-80">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nome, e-mail ou CNPJ..." class="w-full bg-white border-gray-300 rounded-xl shadow-sm focus:ring-black focus:border-black text-xs pl-4 pr-10 py-2.5">
                    @if(request('search'))
                        <a href="{{ route('admin.organizers.index') }}" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 text-xs font-bold">✕</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white border border-gray-100 shadow-xl sm:rounded-3xl p-6 sm:p-8">
            <div class="overflow-x-auto border border-gray-200 rounded-2xl">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead class="bg-gray-50 text-gray-500 uppercase font-bold">
                        <tr>
                            <th class="px-6 py-4">Organizador</th>
                            <th class="px-6 py-4">CNPJ / E-mail</th>
                            <th class="px-6 py-4">Taxa Plataforma</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700 bg-white">
                        @forelse($organizers as $org)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-full bg-gray-100 border border-gray-200 text-gray-800 font-extrabold flex items-center justify-center text-xs uppercase">
                                            {{ substr($org->nome, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 text-sm">{{ $org->nome }}</div>
                                            <div class="text-[11px] text-gray-500">Cadastrado em {{ $org->created_at ? $org->created_at->format('d/m/Y') : '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-mono text-gray-800 font-semibold">{{ $org->email }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $org->cnpj ?: 'Sem CNPJ' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-mono font-bold border {{ $org->tipo_taxa_plataforma === 'porcentagem' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                                        {{ $org->tipo_taxa_plataforma === 'porcentagem' ? $org->taxa_plataforma.'%' : 'R$ '.number_format($org->taxa_plataforma, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($org->ativo)
                                        <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            ● Ativo
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase bg-red-50 text-red-700 border border-red-200">
                                            ● Inativo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-3">
                                    <a href="{{ route('admin.organizers.show', $org->id) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">📊 Financeiro & Eventos</a>
                                    <a href="{{ route('admin.organizers.edit', $org->id) }}" class="text-gray-700 hover:text-black font-bold">✏️ Editar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">Nenhum organizador encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($organizers->hasPages())
                <div class="mt-6">
                    {{ $organizers->links() }}
                </div>
            @endif
        </div>

    </main>

</body>
</html>