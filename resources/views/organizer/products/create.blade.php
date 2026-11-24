@extends('layouts.organizer')

@section('title', 'Novo Produto')

@section('content')

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Criar Novo Produto</h2>
            <p class="text-sm text-gray-500 mt-1">Cadastre itens para vender como add-ons (Camisetas, Copos, Estacionamento).</p>
        </div>
        <a href="{{ route('organizer.products.index') }}" class="text-sm font-bold text-gray-500 hover:text-black transition">
            &larr; Voltar
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl relative mb-6 text-sm font-medium">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('organizer.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- 1. DADOS BÁSICOS -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Informações do Produto</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Título do Produto *</label>
                    <input type="text" name="titulo" value="{{ old('titulo') }}" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm" placeholder="Ex: Camiseta Oficial do Evento">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Categoria *</label>
                    <input type="text" name="categoria" value="{{ old('categoria') }}" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm" placeholder="Ex: Vestuário, Alimentação">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Tipo de Produto *</label>
                    <select name="tipo" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                        <option value="fisico">Físico (Ex: Camiseta, Copo)</option>
                        <option value="digital">Digital / Serviço (Ex: Estacionamento, VIP)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Preço Base (R$) *</label>
                    <input type="number" step="0.01" name="preco_base" value="{{ old('preco_base', '0.00') }}" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                    <p class="text-xs text-gray-500 mt-1">Se o valor depender apenas do tamanho, deixe 0.00 aqui.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Foto do Produto</label>
                    <input type="file" name="foto" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-black hover:file:bg-gray-200">
                </div>
            </div>
        </div>

        <!-- 2. VARIAÇÕES / ATRIBUTOS -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                <h3 class="text-lg font-bold text-gray-900">Variações e Estoque</h3>
                <button type="button" onclick="adicionarVariacao()" class="text-sm bg-gray-100 hover:bg-gray-200 text-black font-bold py-1.5 px-3 rounded-lg transition">
                    + Adicionar Variação
                </button>
            </div>
            
            <p class="text-sm text-gray-500 mb-4">Se for camiseta, adicione: P, M, G. Se for um produto único (ex: Copo), crie uma variação chamada "Único".</p>

            <div id="variacoes-container" class="space-y-4">
                <!-- Linha de Variação Padrão -->
                <div class="flex flex-col sm:flex-row gap-4 items-end bg-gray-50 p-4 rounded-xl border border-gray-200 variacao-row">
                    <div class="w-full sm:w-1/3">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nome (Tamanho/Tipo)</label>
                        <input type="text" name="atributos[0][nome]" required placeholder="Ex: M" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                    </div>
                    <div class="w-full sm:w-1/3">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Preço Adicional (R$)</label>
                        <input type="number" step="0.01" name="atributos[0][preco_adicional]" value="0.00" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                    </div>
                    <div class="w-full sm:w-1/4">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Estoque</label>
                        <input type="number" name="atributos[0][estoque]" placeholder="Vazio = Ilimitado" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                    </div>
                    <div class="w-full sm:w-auto">
                        <span class="block text-xs font-bold text-transparent mb-1 select-none">Ação</span>
                        <button type="button" onclick="removerVariacao(this)" class="w-full sm:w-auto bg-red-100 hover:bg-red-200 text-red-600 font-bold px-4 rounded-lg transition text-sm h-[38px] flex items-center justify-center">
                            Remover
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTÕES -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('organizer.products.index') }}" class="inline-flex items-center px-6 py-3 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold rounded-xl shadow-sm transition">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center px-8 py-3 bg-black hover:bg-gray-800 text-white font-bold rounded-xl shadow-md transition">
                Salvar Produto
            </button>
        </div>
    </form>

    <!-- SCRIPT PARA ADICIONAR VARIAÇÕES DINAMICAMENTE -->
    <script>
        let variacaoIndex = 1;

        function adicionarVariacao() {
            const container = document.getElementById('variacoes-container');
            
            const html = `
                <div class="flex flex-col sm:flex-row gap-4 items-end bg-gray-50 p-4 rounded-xl border border-gray-200 variacao-row mt-4">
                    <div class="w-full sm:w-1/3">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nome (Tamanho/Tipo)</label>
                        <input type="text" name="atributos[${variacaoIndex}][nome]" required placeholder="Ex: G" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                    </div>
                    <div class="w-full sm:w-1/3">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Preço Adicional (R$)</label>
                        <input type="number" step="0.01" name="atributos[${variacaoIndex}][preco_adicional]" value="0.00" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                    </div>
                    <div class="w-full sm:w-1/4">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Estoque</label>
                        <input type="number" name="atributos[${variacaoIndex}][estoque]" value="0" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                        </div>
                    <div class="w-full sm:w-auto">
                        <span class="block text-xs font-bold text-transparent mb-1 select-none">Ação</span>
                        <button type="button" onclick="removerVariacao(this)" class="w-full sm:w-auto bg-red-100 hover:bg-red-200 text-red-600 font-bold px-4 rounded-lg transition text-sm h-[38px] flex items-center justify-center">
                            Remover
                        </button>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
            variacaoIndex++;
        }

        function removerVariacao(botao) {
            const linhas = document.querySelectorAll('.variacao-row');
            if (linhas.length > 1) {
                botao.closest('.variacao-row').remove();
            } else {
                alert('Você precisa ter pelo menos uma variação (Ex: "Único").');
            }
        }
    </script>
@endsection