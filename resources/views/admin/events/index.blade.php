<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Eventos Globais - Super Admin</title>
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
                    <a href="{{ route('admin.organizers.index') }}" class="text-sm font-semibold text-gray-400 hover:text-white transition pb-1">Organizadores</a>
                    <a href="{{ route('admin.events.index') }}" class="text-sm font-bold text-white border-b-2 border-white pb-1">Todos os Eventos</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full space-y-8">

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-bold shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Eventos da Plataforma</h1>
                <p class="text-xs text-gray-500 mt-1">Gerencie todos os eventos criados pelos organizadores e realize exclusão segura</p>
            </div>
            
            <form method="GET" action="{{ route('admin.events.index') }}" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <select name="organizer_id" onchange="this.form.submit()" class="bg-white border-gray-300 rounded-xl text-xs py-2.5 px-3 font-semibold text-gray-800 shadow-sm focus:ring-black focus:border-black">
                    <option value="">Todos os Organizadores</option>
                    @foreach($organizadores as $org)
                        <option value="{{ $org->id }}" {{ request('organizer_id') == $org->id ? 'selected' : '' }}>
                            {{ $org->nome }}
                        </option>
                    @endforeach
                </select>

                <div class="relative w-full sm:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por título ou cidade..." class="w-full bg-white border-gray-300 rounded-xl shadow-sm focus:ring-black focus:border-black text-xs pl-4 pr-10 py-2.5">
                    @if(request('search'))
                        <a href="{{ route('admin.events.index') }}" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 text-xs font-bold">✕</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white border border-gray-100 shadow-xl sm:rounded-3xl p-6 sm:p-8">
            <div class="overflow-x-auto border border-gray-200 rounded-2xl">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead class="bg-gray-50 text-gray-500 uppercase font-bold">
                        <tr>
                            <th class="px-6 py-4">Evento</th>
                            <th class="px-6 py-4">Organizador</th>
                            <th class="px-6 py-4">Cidade / Data</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700 bg-white">
                        @forelse($events as $event)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-6 py-4 font-bold text-gray-900">
                                    <div class="text-sm font-bold text-gray-900">{{ $event->titulo }}</div>
                                    <div class="text-[11px] text-gray-500 font-normal">Slug: {{ $event->slug }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($event->organizer)
                                        <a href="{{ route('admin.organizers.show', $event->organizer->id) }}" class="text-indigo-600 hover:underline font-bold">
                                            {{ $event->organizer->nome }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">Sem Organizador</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div>{{ $event->cidade }}/{{ $event->estado }}</div>
                                    <div class="text-[11px] text-gray-500">{{ \Carbon\Carbon::parse($event->data_inicio)->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase {{ $event->status === 'ativo' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $event->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-3">
                                    <a href="{{ route('event.show', $event->slug) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-bold">Ver Site ↗</a>
                                    <button type="button" onclick="confirmDeleteEvent('{{ $event->id }}', '{{ addslashes($event->titulo) }}')" class="text-red-600 hover:text-red-800 font-bold">
                                        🗑️ Excluir Evento
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">Nenhum evento encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($events->hasPages())
                <div class="mt-6">
                    {{ $events->links() }}
                </div>
            @endif
        </div>

    </main>

    <!-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO SEGURA DE EVENTO -->
    <div id="deleteModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-gray-100 text-left">
            <div class="flex items-center space-x-3 text-red-600 mb-4">
                <span class="text-2xl">⚠️</span>
                <h3 class="text-lg font-bold text-gray-900">Exclusão Segura de Evento</h3>
            </div>
            
            <p class="text-xs text-gray-600 mb-4 leading-relaxed">
                Você está prestes a excluir o evento <strong id="deleteEventTitle" class="text-gray-900 font-bold"></strong>.
            </p>

            <div class="bg-red-50 border border-red-200 rounded-2xl p-4 text-[11px] text-red-800 space-y-1.5 mb-6">
                <div class="font-bold text-red-900">Esta ação irá remover permanentemente:</div>
                <div>• Ingressos e lotes cadastrados do evento</div>
                <div>• Categorias e Cupons vinculados</div>
                <div>• Pedidos e inscrições de atletas</div>
                <div>• Imagens do banner e cards salvas no servidor</div>
            </div>

            <form id="deleteEventForm" method="POST" action="">
                @csrf
                @method('DELETE')

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteModal()" class="text-xs font-bold text-gray-500 hover:text-gray-800 px-4 py-2.5 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl transition shadow-md">
                        Sim, Excluir Evento
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function confirmDeleteEvent(eventId, title) {
            document.getElementById('deleteEventTitle').innerText = title;
            const form = document.getElementById('deleteEventForm');
            form.action = `/admin/events/${eventId}`;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>

</body>
</html>
