@extends('layouts.organizer')

@section('title', 'Novo Ingresso - ' . $event->titulo)

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Adicionar Ingresso / Lote</h2>
            <p class="text-sm text-gray-500 mt-1">Evento: <strong>{{ $event->titulo }}</strong></p>
        </div>
        <a href="{{ route('organizer.events.tickets.index', $event->id) }}" class="text-sm font-bold text-gray-500 hover:text-black transition">
            &larr; Voltar
        </a>
    </div>

    <!-- PUXA O SUBMENU AUTOMÁTICO -->
    @include('organizer.partials.submenu')

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100 p-6 sm:p-8">
        
        <form action="{{ route('organizer.events.tickets.store', $event->id) }}" method="POST" class="space-y-8">
            @csrf

            <!-- BLOCO 1: Informações Básicas -->
            <div>
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b pb-2">Informações Básicas</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-medium text-sm text-gray-700 mb-1">Nome do Ingresso *</label>
                        <input type="text" name="nome" value="{{ old('nome') }}" placeholder="Ex: Lote 1 - Inteira" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                        @error('nome') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>

                    <!-- MODALIDADE -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block font-medium text-sm text-gray-700">Modalidade / Categoria *</label>
                            <a href="{{ route('organizer.events.categories.index', $event->id) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 underline transition">
                                Gerenciar categorias &rarr;
                            </a>
                        </div>
                        <select name="modalidade" id="modalidade_select" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                            <option value="" disabled selected>Selecione uma modalidade...</option>
                            @foreach($modalidades as $mod)
                                <option value="{{ $mod }}" {{ old('modalidade') == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                            @endforeach
                        </select>
                        @error('modalidade') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block font-medium text-sm text-gray-700 mb-1">Descrição (Opcional)</label>
                    <textarea name="descricao" rows="2" placeholder="Ex: Dá direito a um kit atleta especial..." class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">{{ old('descricao') }}</textarea>
                </div>
            </div>

            <!-- BLOCO 2: Valores e Taxas -->
            <div>
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b pb-2">Valores, Quantidade e Taxa</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block font-medium text-sm text-gray-700 mb-1">Preço (BRL) *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 font-medium">R$</span>
                            <input type="number" step="0.01" min="10.00" name="preco" value="{{ old('preco') }}" required class="w-full pl-10 border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1">Valor mínimo: R$ 10,00</p>
                        @error('preco') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-medium text-sm text-gray-700 mb-1">Quantidade Total *</label>
                        <input type="number" name="quantidade_total" min="0" value="{{ old('quantidade_total') }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                        <p class="text-[10px] text-gray-500 mt-1">Digite <strong>0</strong> para lote ilimitado.</p>
                    </div>

                    <div>
                        <label class="block font-medium text-sm text-gray-700 mb-1">Taxa do Organizador *</label>
                        <select name="taxa_organizador" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                            <option value="repassar">Repassar para o comprador</option>
                            <option value="absorver">Absorver (Descontar do valor)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- BLOCO 3: Restrições (Período e Idade) -->
            <div>
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b pb-2">Regras e Restrições</h3>
                
                <!-- Toggle e Bloco de Período -->
                <div class="mb-6 bg-gray-50 p-4 rounded-xl border border-gray-200 transition-all">
                    <label class="relative inline-flex items-center cursor-pointer mb-2">
                        <input type="checkbox" name="restricao_periodo" value="1" class="sr-only peer" id="toggle_periodo" onchange="togglePeriodo()">
                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                        <span class="ml-3 text-sm font-bold text-gray-900">Restringir Período de Vendas</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-4 ml-14">Ative para definir uma data de início e fim para a venda deste lote.</p>

                    <div id="bloco_periodo" class="grid-cols-1 md:grid-cols-2 gap-6 hidden">
                        <div>
                            <label class="block font-medium text-sm text-gray-700 mb-1">Início das Vendas *</label>
                            <input type="datetime-local" name="data_inicio_vendas" id="data_inicio_vendas" value="{{ old('data_inicio_vendas') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700 mb-1">Fim das Vendas *</label>
                            <input type="datetime-local" name="data_fim_vendas" id="data_fim_vendas" value="{{ old('data_fim_vendas') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                        </div>
                    </div>
                </div>

                <!-- Toggle e Bloco de Idade -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 transition-all">
                    <label class="relative inline-flex items-center cursor-pointer mb-2">
                        <input type="checkbox" name="restricao_idade" value="1" class="sr-only peer" id="toggle_idade" onchange="toggleIdade()">
                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                        <span class="ml-3 text-sm font-bold text-gray-900">Restringir por Idade</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-4 ml-14">O comprador só poderá adquirir este ingresso se tiver a idade na faixa configurada abaixo.</p>

                    <div id="bloco_idade" class="grid-cols-1 md:grid-cols-2 gap-6 hidden">
                        <div>
                            <label class="block font-medium text-sm text-gray-700 mb-1">Idade Mínima</label>
                            <input type="number" min="0" name="idade_minima" value="{{ old('idade_minima') }}" placeholder="Ex: 16" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700 mb-1">Idade Máxima</label>
                            <input type="number" min="0" name="idade_maxima" value="{{ old('idade_maxima') }}" placeholder="Ex: 60" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOCO 4: Produtos -->
            <div>
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b pb-2">Produtos Vinculados</h3>
                <p class="text-xs text-gray-500 mb-4">Selecione quais produtos (como camisetas ou kits) poderão ser comprados junto com este ingresso.</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @if(isset($produtos) && $produtos->count() > 0)
                        @foreach($produtos as $produto)
                            <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                                <input type="checkbox" id="prod_{{ $produto->id }}" name="produtos[]" value="{{ $produto->id }}" class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black cursor-pointer">
                                <label for="prod_{{ $produto->id }}" class="ml-3 text-sm font-medium text-gray-900 cursor-pointer w-full">
                                    {{ $produto->titulo }}
                                </label>
                            </div>
                        @endforeach
                    @else
                        <div class="col-span-full p-4 bg-gray-50 text-center rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-500">Você ainda não tem produtos cadastrados. <a href="{{ route('organizer.products.index') }}" class="text-black font-bold hover:underline">Criar Produto</a></p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- BLOCO 5: Desconto Legal (Idoso) -->
            <div>
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b pb-2">Benefícios e Descontos Legais</h3>
                
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 transition-all">
                    <label class="relative inline-flex items-center cursor-pointer mb-1">
                        <input type="checkbox" name="aceita_desconto_idoso" value="1" {{ old('aceita_desconto_idoso') ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                        <span class="ml-3 text-sm font-bold text-gray-900">Aplicar Desconto Legal para Idosos (50%)</span>
                    </label>
                    <p class="text-xs text-gray-500 ml-14">Ative para permitir que compradores com 60 anos ou mais tenham direito automático a 50% de desconto neste ingresso durante o checkout, conforme o Estatuto da Pessoa Idosa.</p>
                </div>
            </div>

            <div class="flex items-center justify-end pt-6 border-t mt-8">
                <button type="submit" class="bg-black hover:bg-gray-800 text-white px-8 py-3 rounded-xl text-sm font-bold shadow-sm transition">
                    Salvar Ingresso
                </button>
            </div>
        </form>

    </div>

    <!-- MODAL DE CRIAÇÃO DE MODALIDADE -->
    <div id="modal_modalidade" class="fixed inset-0 z-50 hidden bg-black bg-opacity-60 flex items-center justify-center p-4 transition-opacity">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Criar categoria</h3>
                    <button type="button" onclick="fecharModalModalidade()" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <p class="text-sm text-gray-500 mb-6">Crie uma categoria para vender vários tipos de ingressos (Ex: 5km, VIP, Meia-entrada).</p>
                
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">Nome da categoria do ingresso *</label>
                    <input type="text" id="nova_modalidade_input" maxlength="50" oninput="atualizarContador()" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black" placeholder="Digite o nome da modalidade">
                    <p class="text-xs text-gray-400 mt-1 text-right"><span id="contador_caracteres">50</span> caracteres restantes</p>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="fecharModalModalidade()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition text-sm">Cancelar</button>
                <button type="button" onclick="salvarModalidade()" class="px-4 py-2 bg-black text-white font-bold rounded-xl hover:bg-gray-800 transition shadow-sm text-sm">Criar categoria</button>
            </div>
        </div>
    </div>

    <!-- SCRIPTS MÁGICOS -->
    <script>
        // Lógica dos Toggles de Restrição
        function togglePeriodo() {
            const isChecked = document.getElementById('toggle_periodo').checked;
            const bloco = document.getElementById('bloco_periodo');
            const inputInicio = document.getElementById('data_inicio_vendas');
            const inputFim = document.getElementById('data_fim_vendas');
            
            if(isChecked) {
                bloco.classList.remove('hidden');
                bloco.classList.add('grid');
                inputInicio.required = true;
                inputFim.required = true;
            } else {
                bloco.classList.add('hidden');
                bloco.classList.remove('grid');
                inputInicio.required = false;
                inputFim.required = false;
                inputInicio.value = '';
                inputFim.value = '';
            }
        }

        function toggleIdade() {
            const isChecked = document.getElementById('toggle_idade').checked;
            const bloco = document.getElementById('bloco_idade');
            const inputMin = document.querySelector('input[name="idade_minima"]');
            const inputMax = document.querySelector('input[name="idade_maxima"]');
            
            if(isChecked) {
                bloco.classList.remove('hidden');
                bloco.classList.add('grid');
            } else {
                bloco.classList.add('hidden');
                bloco.classList.remove('grid');
                inputMin.value = '';
                inputMax.value = '';
            }
        }

        // Lógica do Modal de Modalidade
        function abrirModalModalidade() {
            document.getElementById('modal_modalidade').classList.remove('hidden');
            setTimeout(() => document.getElementById('nova_modalidade_input').focus(), 100);
        }

        function fecharModalModalidade() {
            document.getElementById('modal_modalidade').classList.add('hidden');
            document.getElementById('nova_modalidade_input').value = '';
            atualizarContador();
        }

        function atualizarContador() {
            const limite = 50;
            const digitado = document.getElementById('nova_modalidade_input').value.length;
            document.getElementById('contador_caracteres').innerText = limite - digitado;
        }

        function salvarModalidade() {
            const input = document.getElementById('nova_modalidade_input');
            const valor = input.value.trim();
            
            if (valor === '') {
                alert('Por favor, digite um nome para a categoria.');
                input.focus();
                return;
            }

            // Adiciona a nova opção no Select
            const select = document.getElementById('modalidade_select');
            const option = document.createElement('option');
            option.value = valor;
            option.text = valor;
            select.appendChild(option);
            
            // Seleciona a opção recém-criada
            select.value = valor;
            
            // Fecha o modal
            fecharModalModalidade();
        }
    </script>
@endsection