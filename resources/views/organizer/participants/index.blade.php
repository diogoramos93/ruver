@extends('layouts.organizer')

@section('title', 'Participantes - ' . $event->titulo)

@section('content')

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Gerenciar Evento: {{ $event->titulo }}</h2>
        <a href="{{ route('organizer.events.participants.index', array_filter(['event' => $event->id, 'export' => 'excel', 'search' => request('search'), 'com_anexo' => request('com_anexo')])) }}" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Exportar Excel
        </a>
    </div>

    <!-- PUXA O SUBMENU AUTOMÁTICO -->
    @include('organizer.partials.submenu')

    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-bold shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-2 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- LISTAGEM DE PARTICIPANTES -->
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100 p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-6">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Lista de Inscritos</h3>
                <p class="text-xs text-gray-500">Total de {{ $participantes->total() }} participante(s) encontrado(s)</p>
            </div>
            
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                <!-- BOTÃO DO FILTRO: TODOS VS APENAS COM ANEXO -->
                <div class="inline-flex rounded-xl p-1 bg-gray-100 border border-gray-200 text-xs font-bold self-start sm:self-auto">
                    <a href="{{ route('organizer.events.participants.index', array_filter(['event' => $event->id, 'search' => request('search')])) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ !request('com_anexo') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        Todos
                    </a>
                    <a href="{{ route('organizer.events.participants.index', array_filter(['event' => $event->id, 'com_anexo' => 1, 'search' => request('search')])) }}" 
                       class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ request('com_anexo') == '1' ? 'bg-black text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        <span>📷</span>
                        <span>Com Anexo</span>
                    </a>
                </div>

                <form method="GET" action="{{ route('organizer.events.participants.index', $event->id) }}" class="w-full sm:w-72">
                    @if(request('com_anexo'))
                        <input type="hidden" name="com_anexo" value="1">
                    @endif
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nome, CPF ou equipe..." class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-black focus:border-black text-sm pl-4 pr-10 py-2">
                        @if(request('search'))
                            <a href="{{ route('organizer.events.participants.index', array_filter(['event' => $event->id, 'com_anexo' => request('com_anexo')])) }}" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 text-xs font-bold">✕</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        
        <div class="overflow-x-auto border border-gray-200 rounded-2xl">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Participante</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Documento / Nasc.</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Modalidade</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Produtos / Kit</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Anexo / Imagem</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status & Voucher</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($participantes as $participante)
                        <tr class="hover:bg-gray-50/50 transition">
                            <!-- 1. Participante -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-100 border border-gray-200 rounded-full flex items-center justify-center font-bold text-gray-700 text-sm uppercase">
                                        {{ substr($participante->nome, 0, 2) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $participante->nome }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $participante->cidade ?? 'Cidade não informada' }} 
                                            @if($participante->equipe) • <span class="font-semibold text-gray-700">{{ $participante->equipe }}</span> @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 2. Documento / Nasc -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="font-mono text-xs font-bold text-gray-900">
                                    {{ $participante->cpf ?: ($participante->passaporte ?: 'Sem documento') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Nasc: {{ $participante->data_nascimento ? \Carbon\Carbon::parse($participante->data_nascimento)->format('d/m/Y') : '-' }} 
                                    ({{ $participante->sexo }})
                                </div>
                            </td>

                            <!-- 3. Modalidade / Ingresso -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $participante->ticket ? $participante->ticket->nome : 'N/A' }}
                                </span>
                            </td>

                            <!-- 4. Produtos / Kits -->
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div class="flex flex-col items-start gap-1.5">
                                    @php
                                        // Filtra para ignorar itens vazios ou nulos
                                        $produtosValidos = is_array($participante->produtos) 
                                            ? array_filter($participante->produtos, function($v) { return !empty($v); }) 
                                            : [];
                                    @endphp

                                    @if(count($produtosValidos) > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($produtosValidos as $key => $val)
                                                @php
                                                    $label = $val;
                                                    if (is_numeric($val) && isset($attributesMap[$val])) {
                                                        $label = $attributesMap[$val];
                                                    } elseif (is_numeric($key) && isset($attributesMap[$key])) {
                                                        $label = $attributesMap[$key];
                                                    } elseif (!is_numeric($key)) {
                                                        $label = "$key: $val";
                                                    }
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                                    {{ $label }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">Nenhum kit</span>
                                    @endif

                                    <button type="button" onclick="openProductModal('{{ $participante->id }}', '{{ addslashes($participante->nome) }}', {{ json_encode($participante->produtos) }})" class="inline-flex items-center text-[11px] font-bold text-gray-700 hover:text-black bg-gray-100 hover:bg-gray-200 px-2.5 py-1 rounded-lg border border-gray-200 transition">
                                        @if(count($produtosValidos) > 0)
                                            ✏️ Alterar
                                        @else
                                            +Vincular
                                        @endif
                                    </button>
                                </div>
                            </td>

                            <!-- 5. Anexo / Imagem -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $anexosImagem = $participante->formAnswers ? $participante->formAnswers->filter(function($ans) {
                                        return $ans->tipo === 'image' || (is_string($ans->resposta) && str_starts_with($ans->resposta, 'respostas_formularios/'));
                                    }) : collect();
                                @endphp

                                @if($anexosImagem->isNotEmpty())
                                    <div class="flex flex-col items-start gap-1.5">
                                        @foreach($anexosImagem as $anexo)
                                            @php
                                                $urlAnexo = asset('storage/' . $anexo->resposta);
                                                $perguntaTitulo = $anexo->pergunta ?: 'Anexo de Imagem';
                                            @endphp
                                            <button type="button" 
                                                    onclick="abrirModalImagem('{{ $urlAnexo }}', '{{ addslashes($participante->nome) }}', '{{ addslashes($perguntaTitulo) }}')" 
                                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2.5 py-1 rounded-lg transition shadow-2xs">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                Ver Anexo
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">Sem anexo</span>
                                @endif
                            </td>

                            <!-- 6. Pagamento / Voucher -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center space-x-2">
                                    @if($participante->order && $participante->order->status === 'pago')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            ● Pago
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            ● {{ ucfirst($participante->order->status ?? 'Pendente') }}
                                        </span>
                                    @endif

                                    <a href="{{ route('ticket.show', $participante->id) }}" target="_blank" class="text-xs font-bold text-gray-700 hover:text-black bg-gray-100 hover:bg-gray-200 py-1 px-2.5 rounded-lg border border-gray-200 transition">
                                        Voucher ↗
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p class="text-base font-bold text-gray-900">Nenhum participante encontrado</p>
                                <p class="text-sm text-gray-500 mt-1">
                                    @if(request('search') || request('com_anexo'))
                                        Nenhum resultado para os filtros atuais. <a href="{{ route('organizer.events.participants.index', $event->id) }}" class="text-black font-bold underline">Limpar filtros</a>
                                    @else
                                        A lista será preenchida automaticamente assim que as compras de ingressos forem realizadas.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if($participantes->hasPages())
            <div class="mt-6">
                {{ $participantes->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL DE EDIÇÃO DE PRODUTO DO PARTICIPANTE -->
    <div id="productModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Gestão de Kit / Produto</span>
                    <h3 class="text-xl font-extrabold text-gray-900 mt-0.5" id="modalParticipantName">Nome do Participante</h3>
                </div>
                <button type="button" onclick="closeProductModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold p-1">✕</button>
            </div>

            <form id="productModalForm" method="POST" action="">
                @csrf
                @method('PUT')

                <input type="hidden" name="remover_produto" id="inputRemoverProduto" value="0">

                <div class="mb-6 max-h-[50vh] overflow-y-auto space-y-4 pr-1">
                    <p class="text-xs text-gray-500 mb-2">Selecione as variações de cada produto/kit para este participante. Você pode vincular quantos produtos desejar:</p>
                    
                    @forelse($produtosDisponiveis as $prod)
                        <div class="p-3.5 rounded-2xl border border-gray-200 bg-gray-50/70 hover:bg-gray-50 transition">
                            <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                <span>📦 {{ $prod->titulo }}</span>
                            </label>
                            <select name="produtos[{{ $prod->id }}]" id="select_prod_{{ $prod->id }}" class="prod-attr-select w-full border-gray-300 rounded-xl shadow-xs focus:ring-black focus:border-black text-xs font-semibold text-gray-800 bg-white p-2.5">
                                <option value="">(Nenhum / Não vinculado)</option>
                                @foreach($prod->attributes as $attr)
                                    <option value="{{ $attr->id }}">{{ $attr->nome }} @if($attr->preco_adicional > 0) (+R$ {{ number_format($attr->preco_adicional, 2, ',', '.') }}) @endif</option>
                                @endforeach
                            </select>
                        </div>
                    @empty
                        <div class="p-4 rounded-xl bg-gray-50 text-xs text-gray-500 text-center">
                            Nenhum produto cadastrado no seu painel.
                        </div>
                    @endforelse
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-100">
                    <button type="button" id="btnRemoverProduto" onclick="submitRemoverProduto()" class="w-full sm:w-auto text-xs font-bold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 py-2.5 px-4 rounded-xl border border-red-200 transition text-center">
                        🗑️ Desvincular Tudo
                    </button>
                    <div class="flex items-center space-x-2 w-full sm:w-auto justify-end">
                        <button type="button" onclick="closeProductModal()" class="text-xs font-bold text-gray-500 hover:text-gray-800 py-2.5 px-4 rounded-xl transition">
                            Cancelar
                        </button>
                        <button type="submit" class="bg-black hover:bg-gray-800 text-white text-xs font-bold py-2.5 px-5 rounded-xl transition shadow-sm">
                            Salvar Produtos
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPT DO MODAL (Mantido fora do layout, pois é específico desta página) -->
    <script>
        function openProductModal(participantId, name, currentProdutos) {
            document.getElementById('modalParticipantName').innerText = name;
            const form = document.getElementById('productModalForm');
            form.action = `/painel/events/{{ $event->id }}/participantes/${participantId}/produtos`;
            document.getElementById('inputRemoverProduto').value = "0";

            // Reseta todos os selects para vazio
            document.querySelectorAll('.prod-attr-select').forEach(sel => sel.value = "");

            if (currentProdutos) {
                if (typeof currentProdutos === 'object' && currentProdutos !== null) {
                    for (const [key, val] of Object.entries(currentProdutos)) {
                        // Tenta pelo ID do produto
                        const selectByProd = document.getElementById('select_prod_' + key);
                        if (selectByProd && selectByProd.querySelector(`option[value="${val}"]`)) {
                            selectByProd.value = val;
                        } else {
                            // Se a chave não for o prodId, procura em todos os selects quem contém esse atributo ID
                            document.querySelectorAll('.prod-attr-select').forEach(sel => {
                                const opt = sel.querySelector(`option[value="${val}"]`) || sel.querySelector(`option[value="${key}"]`);
                                if (opt) {
                                    sel.value = opt.value;
                                }
                            });
                        }
                    }
                } else if (typeof currentProdutos === 'number' || typeof currentProdutos === 'string') {
                    document.querySelectorAll('.prod-attr-select').forEach(sel => {
                        const opt = sel.querySelector(`option[value="${currentProdutos}"]`);
                        if (opt) sel.value = currentProdutos;
                    });
                }
            }

            document.getElementById('productModal').classList.remove('hidden');
        }

        function closeProductModal() {
            document.getElementById('productModal').classList.add('hidden');
        }

        function submitRemoverProduto() {
            if (confirm('Tem certeza que deseja desvincular todos os produtos deste participante?')) {
                document.getElementById('inputRemoverProduto').value = "1";
                document.getElementById('productModalForm').submit();
            }
        }

        function abrirModalImagem(url, participante, pergunta) {
            document.getElementById('imageModalImg').src = url;
            document.getElementById('imageModalDownload').href = url;
            document.getElementById('imageModalParticipante').textContent = 'Participante: ' + participante;
            document.getElementById('imageModalPergunta').textContent = pergunta;
            document.getElementById('imagePreviewModal').classList.remove('hidden');
        }

        function fecharModalImagem() {
            document.getElementById('imagePreviewModal').classList.add('hidden');
            document.getElementById('imageModalImg').src = '';
        }

        // Fechar com clique fora do modal
        document.getElementById('imagePreviewModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalImagem();
            }
        });
    </script>

    <!-- MODAL DE VISUALIZAÇÃO DE ANEXO/IMAGEM -->
    <div id="imagePreviewModal" class="fixed inset-0 bg-black/75 z-50 flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-gray-100 flex flex-col max-h-[90vh]">
            <div class="flex items-start justify-between pb-3 border-b border-gray-100 mb-4">
                <div>
                    <h3 id="imageModalPergunta" class="text-base font-extrabold text-gray-900">Anexo do Participante</h3>
                    <p id="imageModalParticipante" class="text-xs text-gray-500 mt-0.5 font-medium">Participante</p>
                </div>
                <button type="button" onclick="fecharModalImagem()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition font-bold text-lg leading-none">✕</button>
            </div>
            
            <div class="overflow-auto flex-1 flex items-center justify-center p-3 bg-gray-50 rounded-2xl border border-gray-200">
                <img id="imageModalImg" src="" alt="Anexo do participante" class="max-h-[60vh] max-w-full rounded-xl object-contain shadow-sm">
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                <a id="imageModalDownload" href="" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-700 hover:text-black bg-gray-100 hover:bg-gray-200 px-4 py-2.5 rounded-xl border border-gray-200 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Abrir Imagem em Nova Aba ↗
                </a>
                <button type="button" onclick="fecharModalImagem()" class="bg-black text-white hover:bg-gray-800 text-xs font-bold px-6 py-2.5 rounded-xl transition shadow-sm">
                    Fechar
                </button>
            </div>
        </div>
    </div>
@endsection