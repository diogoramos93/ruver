@extends('layouts.organizer')

@section('title', 'Cupons - ' . $event->titulo)

@section('content')

    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Gerenciar Evento: {{ $event->titulo }}</h2>
    </div>

    <!-- PUXA O SUBMENU AUTOMÁTICO DO NOVO CAMINHO -->
    @include('organizer.partials.submenu')

    <!-- ALERTAS DE SUCESSO/ERRO -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-bold shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-bold shadow-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6 border-b pb-2">Novo Cupom</h3>
                
                <form action="{{ route('organizer.events.coupons.store', $event->id) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block font-medium text-sm text-gray-700 mb-1">Nome do Cupom *</label>
                        <input type="text" name="nome" value="{{ old('nome') }}" required placeholder="Ex: Promocional de Inverno" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                    </div>

                    <div>
                        <label class="block font-medium text-sm text-gray-700 mb-1">Código do Cupom *</label>
                        <input type="text" name="codigo" value="{{ old('codigo') }}" required placeholder="Ex: INVERNO20" style="text-transform: uppercase;" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black font-mono">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-sm text-gray-700 mb-1">Tipo *</label>
                            <select name="tipo_desconto" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                                <option value="" disabled selected>Selecione...</option>
                                <option value="fixo" {{ old('tipo_desconto') == 'fixo' ? 'selected' : '' }}>Fixo (R$)</option>
                                <option value="porcentagem" {{ old('tipo_desconto') == 'porcentagem' ? 'selected' : '' }}>Porcentagem (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700 mb-1">Valor *</label>
                            <input type="number" step="0.01" min="0" name="valor_desconto" value="{{ old('valor_desconto') }}" required placeholder="Ex: 15.00" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium text-sm text-gray-700 mb-1">Data de Início *</label>
                        <input type="datetime-local" name="data_inicio" value="{{ old('data_inicio') }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                    </div>

                    <div>
                        <label class="block font-medium text-sm text-gray-700 mb-1">Data de Término *</label>
                        <input type="datetime-local" name="data_termino" value="{{ old('data_termino') }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                    </div>

                    <div>
                        <label class="block font-medium text-sm text-gray-700 mb-1">Limite de Uso (Opcional)</label>
                        <input type="number" min="1" name="quantidade_total" value="{{ old('quantidade_total') }}" placeholder="Ex: 100" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                        <p class="text-[10px] text-gray-500 mt-1">Deixe em branco para uso ilimitado.</p>
                    </div>

                    <div class="pt-4 border-t mt-4">
                        <button type="submit" class="w-full bg-black hover:bg-gray-800 text-white font-bold py-3 px-4 rounded-xl text-sm transition shadow-sm">
                            Salvar Cupom
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- LISTAGEM DE CUPONS -->
        <div class="lg:col-span-2">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Cupons Cadastrados</h3>
                
                <div class="overflow-x-auto border border-gray-200 rounded-xl">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Código / Nome</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Desconto</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Uso</th>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($coupons as $coupon)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900 bg-gray-100 px-2 py-1 rounded inline-block mb-1">{{ $coupon->codigo }}</div>
                                        <div class="text-xs text-gray-500">{{ $coupon->nome }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">{{ $coupon->valor_formatado }}</div>
                                        <div class="text-xs text-gray-500">Val: {{ \Carbon\Carbon::parse($coupon->data_termino)->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="font-bold">{{ $coupon->quantidade_usada }}</span> / 
                                        {{ $coupon->quantidade_total ? $coupon->quantidade_total : '∞' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($coupon->ativo)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <form action="{{ route('organizer.events.coupons.toggle', [$event->id, $coupon->id]) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                @if($coupon->ativo)
                                                    <button type="submit" class="text-orange-600 hover:text-orange-900 font-bold bg-orange-50 px-3 py-1.5 rounded-lg hover:bg-orange-100 transition">Desativar</button>
                                                @else
                                                    <button type="submit" class="text-green-600 hover:text-green-900 font-bold bg-green-50 px-3 py-1.5 rounded-lg hover:bg-green-100 transition">Ativar</button>
                                                @endif
                                            </form>
                                            <form action="{{ route('organizer.events.coupons.destroy', [$event->id, $coupon->id]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este cupom permanentemente?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-bold bg-red-50 px-3 py-1.5 rounded-lg hover:bg-red-100 transition">Excluir</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <p class="text-sm text-gray-500">Nenhum cupom de desconto cadastrado para este evento.</p>
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