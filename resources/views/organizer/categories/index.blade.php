@extends('layouts.organizer')

@section('title', 'Categorias - ' . $event->titulo)

@section('content')

    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Gerenciar Evento: {{ $event->titulo }}</h2>
    </div>

    <!-- PUXA O SUBMENU AUTOMÁTICO -->
    @include('organizer.partials.submenu')

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-bold shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- FORMULÁRIO DE CRIAÇÃO -->
        <div class="lg:col-span-1">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Nova Categoria</h3>
                <p class="text-sm text-gray-500 mb-6">Crie modalidades para agrupar seus ingressos (Ex: 5km, VIP, Meia-Entrada).</p>
                
                <form action="{{ route('organizer.events.categories.store', $event->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 mb-1">Nome da Categoria *</label>
                        <input type="text" name="nome" required placeholder="Ex: 5km" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                        @error('nome') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="w-full bg-black hover:bg-gray-800 text-white font-bold py-2.5 px-4 rounded-xl text-sm transition shadow-sm">
                        Adicionar Categoria
                    </button>
                </form>
            </div>
        </div>

        <!-- LISTAGEM DE CATEGORIAS -->
        <div class="lg:col-span-2">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Categorias Cadastradas</h3>
                
                <div class="overflow-x-auto border border-gray-200 rounded-xl">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nome</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($categories as $category)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">{{ $category->nome }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <form action="{{ route('organizer.events.categories.destroy', [$event->id, $category->id]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta categoria? Os ingressos vinculados a ela ficarão sem categoria.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-bold bg-red-50 px-3 py-1.5 rounded-lg hover:bg-red-100 transition">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-12 text-center">
                                        <p class="text-sm text-gray-500">Nenhuma categoria cadastrada ainda.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection