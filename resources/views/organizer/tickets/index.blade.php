@extends('layouts.organizer')

@section('title', 'Ingressos - ' . $event->titulo)

@section('content')

    <!-- Título da Página -->
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Gerenciar Evento: {{ $event->titulo }}</h2>
    </div>

    <!-- PUXA O SUBMENU AUTOMÁTICO -->
    @include('organizer.partials.submenu')

    <!-- ÁREA DE CONTEÚDO DOS INGRESSOS -->
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100 p-6 sm:p-8">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Lotes / Tipos de Ingressos</h3>
                <p class="text-sm text-gray-500 mt-1">Gerencie os ingressos disponíveis para venda neste evento.</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('organizer.events.index') }}" class="bg-white text-gray-700 border border-gray-300 font-semibold py-2.5 px-4 rounded-xl text-sm hover:bg-gray-50 transition shadow-sm">
                    Voltar
                </a>
                <a href="{{ route('organizer.events.tickets.create', $event->id) }}" class="bg-black text-white font-bold py-2.5 px-5 rounded-xl text-sm hover:bg-gray-800 transition shadow-sm">
                    + Novo Ingresso
                </a>
            </div>
        </div>

        <!-- Mensagem de Sucesso -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-bold shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Mensagem de Erro -->
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-bold shadow-sm">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <!-- AGRUPAMENTO DOS INGRESSOS POR MODALIDADE -->
        @php
            $ticketsAgrupados = collect($tickets)->groupBy(function ($ticket) {
                return trim($ticket->modalidade ?? '') ?: 'Sem modalidade';
            });
        @endphp

        @forelse($ticketsAgrupados as $modalidade => $ingressos)

            <!-- TÍTULO DA MODALIDADE -->
            <div class="flex items-center justify-between border-b border-gray-200 pb-3 mb-4 {{ !$loop->first ? 'mt-8' : '' }}">
                <h3 class="text-lg font-bold text-gray-900 tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-black"></span>
                    {{ $modalidade }}
                </h3>
            </div>

            <!-- TABELA DE INGRESSOS -->
            <div class="overflow-x-auto border border-gray-200 rounded-xl">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nome do Ingresso</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Valor / Taxa</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Período de Vendas</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Produtos</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Disponíveis</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">

                        @foreach($ingressos as $ticket)
                            <tr class="hover:bg-gray-50 transition">
                                
                                <!-- NOME DO INGRESSO -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ $ticket->nome }}</div>
                                    <div class="text-xs text-gray-500">{{ $ticket->modalidade }}</div>
                                </td>

                                <!-- VALOR E TAXA DA PLATAFORMA -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-green-600 mb-1">
                                        R$ {{ number_format($ticket->preco, 2, ',', '.') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <span class="font-semibold text-gray-700">Taxa:</span> 
                                        @if(($event->organizer->tipo_taxa_plataforma ?? '') === 'fixo')
                                            R$ {{ number_format($event->organizer->taxa_plataforma ?? 0, 2, ',', '.') }}
                                        @else
                                            {{ floatval($event->organizer->taxa_plataforma ?? 0) }}%
                                        @endif
                                    </div>
                                </td>

                                <!-- PERÍODO DE VENDAS -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs text-gray-900 mb-1">
                                        <span class="font-bold text-green-600">Início:</span> 
                                        {{ $ticket->data_inicio_vendas ? \Carbon\Carbon::parse($ticket->data_inicio_vendas)->format('d/m/Y H:i') : 'Não definido' }}
                                    </div>
                                    <div class="text-xs text-gray-900">
                                        <span class="font-bold text-red-600">Fim:</span> 
                                        {{ $ticket->data_fim_vendas ? \Carbon\Carbon::parse($ticket->data_fim_vendas)->format('d/m/Y H:i') : 'Não definido' }}
                                    </div>
                                </td>

                                <!-- PRODUTOS VINCULADOS -->
                                <td class="px-6 py-4 whitespace-normal min-w-[150px]">
                                    @if(!empty($ticket->produtos_vinculados))
                                        @php
                                            // Converte o "1,2" num array. Se for JSON, o Laravel também entende.
                                            $ids = is_string($ticket->produtos_vinculados) 
                                                ? explode(',', str_replace(['[', ']', '"'], '', $ticket->produtos_vinculados)) 
                                                : $ticket->produtos_vinculados;
                                            
                                            // Busca os nomes dos produtos na tabela Product
                                            $produtosAssociados = \App\Models\Product::whereIn('id', (array)$ids)->get();
                                        @endphp
                                        
                                        @if($produtosAssociados->count() > 0)
                                            <div class="flex flex-col gap-1.5">
                                                @foreach($produtosAssociados as $produto)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-purple-50 border border-purple-200 text-purple-700 w-fit">
                                                         {{ $produto->titulo }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Nenhum</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400 italic">Nenhum</span>
                                    @endif
                                </td>

                                <!-- DISPONIBILIDADE -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <span class="font-bold {{ $ticket->quantidade_disponivel == 0 ? 'text-red-600' : 'text-blue-600' }}">
                                            {{ $ticket->quantidade_disponivel }}
                                        </span>
                                        <span class="text-gray-400">/ {{ $ticket->quantidade_total }}</span>
                                    </div>
                                </td>

                                <!-- AÇÕES -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex items-center justify-end space-x-2">
                                    <a href="{{ route('organizer.events.tickets.edit', [$event->id, $ticket->id]) }}" class="text-blue-600 hover:text-blue-900 font-bold bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition">Editar</a>

                                    <form action="{{ route('organizer.events.tickets.destroy', [$event->id, $ticket->id]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este lote de ingressos?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-bold bg-red-50 px-3 py-1.5 rounded-lg hover:bg-red-100 transition">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

        @empty
            <!-- NENHUM INGRESSO -->
            <div class="overflow-x-auto border border-gray-200 rounded-xl">
                <table class="min-w-full divide-y divide-gray-200">
                    <tbody class="bg-white">
                        <tr>
                            <!-- Atualizado para 6 colunas -->
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-bold text-gray-900">Nenhum ingresso</h3>
                                <p class="mt-1 text-sm text-gray-500">Comece criando o primeiro lote de ingressos para este evento.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforelse

    </div>

@endsection