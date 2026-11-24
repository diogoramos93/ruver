@extends('layouts.organizer')

@section('title', 'Meus Eventos')

@section('content')

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100 p-6 sm:p-8">
        
        <!-- Cabeçalho da Página -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Meus Eventos</h2>
                <p class="text-sm text-gray-500 mt-1">Gerencie os eventos criados e acompanhe os ingressos.</p>
            </div>
            
            <a href="{{ route('organizer.events.create') }}" class="inline-flex items-center px-5 py-2.5 bg-black hover:bg-gray-800 text-white text-sm font-bold rounded-xl shadow-sm transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Novo Evento
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-2xl relative mb-6 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <!-- Tabela de Eventos -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider rounded-l-xl">Título</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Local</th>
                        <th class="px-6 py-3 bg-gray-50 text-right text-xs font-bold text-gray-500 uppercase tracking-wider rounded-r-xl">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($events as $event)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">{{ $event->titulo }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ \Carbon\Carbon::parse($event->data_inicio)->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $event->endereco ?? $event->cidade }}</td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ route('organizer.events.edit', $event->id) }}" class="inline-flex items-center text-gray-700 hover:text-black bg-gray-100 hover:bg-gray-200 py-1.5 px-3 rounded-lg font-bold text-xs transition">Editar</a>
                                <a href="{{ route('organizer.events.financial', $event->id) }}" class="inline-flex items-center text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 py-1.5 px-3 rounded-lg font-bold text-xs transition">Financeiro</a>
                                <a href="{{ route('organizer.events.tickets.index', $event->id) }}" class="inline-flex items-center text-white bg-black hover:bg-gray-800 py-1.5 px-3 rounded-lg font-bold text-xs transition">Ingressos</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 font-medium">Nenhum evento cadastrado ainda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection