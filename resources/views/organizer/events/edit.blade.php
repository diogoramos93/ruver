@extends('layouts.organizer')

@section('title', 'Editar Evento - ' . $event->titulo)

@section('content')

    <!-- Título da Página -->
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Gerenciar Evento: {{ $event->titulo }}</h2>
    </div>

    <!-- PUXA O SUBMENU AUTOMÁTICO -->
    @include('organizer.partials.submenu')

    <!-- FORMULÁRIO DE EDIÇÃO -->
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100 p-6 sm:p-8">
        
        <form action="{{ route('organizer.events.update', $event->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Mensagem de Sucesso -->
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-bold shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block font-medium text-sm text-gray-700">Título do Evento</label>
                    <input type="text" name="titulo" value="{{ old('titulo', $event->titulo) }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Categoria</label>
                    <input type="text" name="categoria" value="{{ old('categoria', $event->categoria) }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
            </div>

            <!-- CAMPO DE DESCRIÇÃO COM O EDITOR -->
            <div>
                <label class="block font-medium text-sm text-gray-700 mb-2">Descrição</label>
                <textarea name="descricao" id="descricao_editor" rows="10" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">{{ old('descricao', $event->descricao) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block font-medium text-sm text-gray-700">Endereço</label>
                    <input type="text" name="endereco" value="{{ old('endereco', $event->endereco) }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Cidade</label>
                    <input type="text" name="cidade" value="{{ old('cidade', $event->cidade) }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Estado (UF)</label>
                    <input type="text" name="estado" value="{{ old('estado', $event->estado) }}" required maxlength="2" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block font-medium text-sm text-gray-700">CEP</label>
                    <input type="text" name="cep" value="{{ old('cep', $event->cep) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Data de Início</label>
                    <input type="datetime-local" name="data_inicio" value="{{ old('data_inicio', \Carbon\Carbon::parse($event->data_inicio)->format('Y-m-d\TH:i')) }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Data de Término</label>
                    <input type="datetime-local" name="data_termino" value="{{ old('data_termino', $event->data_termino ? \Carbon\Carbon::parse($event->data_termino)->format('Y-m-d\TH:i') : '') }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block font-medium text-sm text-gray-700">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                        <option value="ativo" {{ $event->status == 'ativo' ? 'selected' : '' }}>Ativo</option>
                        <option value="rascunho" {{ $event->status == 'rascunho' ? 'selected' : '' }}>Rascunho</option>
                        <option value="encerrado" {{ $event->status == 'encerrado' ? 'selected' : '' }}>Encerrado</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Política de Reembolso</label>
                    <input type="text" name="politica_reembolso" value="{{ old('politica_reembolso', $event->politica_reembolso) }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Regulamentos</label>
                <textarea name="regulamentos" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">{{ old('regulamentos', $event->regulamentos) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-200 pt-6 mt-6">
                <div>
                    @if($event->banner)
                        <div class="mb-3">
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Banner Atual</span>
                            <img src="{{ asset('storage/' . $event->banner) }}" alt="Banner Atual" class="h-24 w-auto rounded-lg object-cover shadow-sm border border-gray-200">
                        </div>
                    @endif
                    <label class="block font-medium text-sm text-gray-700 mb-1">Alterar Banner (Opcional)</label>
                    <input type="file" name="banner" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition">
                <!-- 👇 NOVO: Checkbox de Destaque na Edição 👇 -->
                    <div class="mt-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="banner_destaque_organizador" value="1" class="rounded border-gray-300 text-black shadow-sm focus:ring-black h-5 w-5" {{ old('banner_destaque_organizador', $event->banner_destaque_organizador) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm font-medium text-gray-900">Destacar no carrossel da página inicial</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-7">O banner principal do evento será exibido no topo do site.</p>
                    </div>
                </div>
                <div>
                    @if($event->imagem_card)
                        <div class="mb-3">
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Card Atual</span>
                            <img src="{{ asset('storage/' . $event->imagem_card) }}" alt="Card Atual" class="h-24 w-auto rounded-lg object-cover shadow-sm border border-gray-200">
                        </div>
                    @endif
                    <label class="block font-medium text-sm text-gray-700 mb-1">Alterar Imagem do Card / Flyer (Opcional)</label>
                    <input type="file" name="imagem_card" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition">
                </div>
            </div>
           
           
            <!-- Bloco de Documentos / PDF -->
            <div class="border-t border-gray-200 pt-6 mt-6">
                
                @php
                    // Garante que o Laravel transforme o texto JSON do banco em uma lista real (array)
                    $documentosSalvos = is_string($event->documentos) ? json_decode($event->documentos, true) : ($event->documentos ?? []);
                @endphp
                
                @if(!empty($documentosSalvos))
                    <div class="mb-4 p-4 bg-gray-50 border border-gray-100 rounded-xl">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Documentos Anexados</span>
                        
                        <div class="flex flex-wrap gap-4">
                            @foreach($documentosSalvos as $index => $doc)
                                <!-- Card do PDF -->
                                <div class="flex flex-col items-center bg-white border border-gray-200 rounded-lg p-3 shadow-sm w-28 group relative">
                                    <!-- Ícone Clicável para abrir o PDF -->
                                    <a href="{{ asset('storage/' . $doc) }}" target="_blank" title="Abrir PDF" class="flex flex-col items-center hover:opacity-80 transition mb-2">
                                        <img src="{{ asset('images/pdf logo.png') }}" alt="Ícone PDF" class="h-10 w-10 object-contain mb-1 group-hover:scale-110 transition-transform">
                                        <span class="text-[10px] text-gray-600 font-bold text-center leading-tight">Anexo {{ $index + 1 }}</span>
                                    </a>
                                    
                                    <!-- 👇 BOTÃO/CHECKBOX DE REMOVER 👇 -->
                                    <div class="w-full bg-red-50 border border-red-100 rounded flex items-center justify-center py-1 mt-auto">
                                        <input type="checkbox" name="remover_documentos[]" value="{{ $doc }}" id="rem_{{ $index }}" class="w-3 h-3 text-red-600 border-gray-300 rounded focus:ring-red-500 cursor-pointer">
                                        <label for="rem_{{ $index }}" class="ml-1 text-[9px] font-bold text-red-600 uppercase cursor-pointer">Remover</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <label class="block font-medium text-sm text-gray-700 mb-1">Adicionar Novos Documentos (PDF)</label>
                <input type="file" name="documentos[]" accept="application/pdf" multiple class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition">
                <p class="text-xs text-gray-400 mt-2">Você pode selecionar vários arquivos segurando Ctrl ou Shift.</p>
            </div>

            <!-- MÉTODOS DE PAGAMENTO -->
            <div class="border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Métodos de Pagamento Permitidos</h3>
                
                <div class="space-y-4">
                    <!-- PIX -->
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="aceita_pix" value="1" class="rounded border-gray-300 text-black shadow-sm focus:ring-black h-5 w-5" {{ old('aceita_pix', $event->aceita_pix) ? 'checked' : '' }}>
                        <span class="ml-3 text-sm font-medium text-gray-700">Aceitar PIX</span>
                    </label>

                    <!-- Cartão de Crédito -->
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="aceita_cartao" value="1" class="rounded border-gray-300 text-black shadow-sm focus:ring-black h-5 w-5" {{ old('aceita_cartao', $event->aceita_cartao) ? 'checked' : '' }}>
                        <span class="ml-3 text-sm font-medium text-gray-700">Aceitar Cartão de Crédito</span>
                    </label>

                    <!-- Boleto -->
                    <div class="p-5 bg-gray-50 rounded-xl border border-gray-200">
                        <label class="flex items-center cursor-pointer mb-4">
                            <input type="checkbox" name="aceita_boleto" value="1" class="rounded border-gray-300 text-black shadow-sm focus:ring-black h-5 w-5" {{ old('aceita_boleto', $event->aceita_boleto) ? 'checked' : '' }}>
                            <span class="ml-3 text-sm font-bold text-gray-900">Aceitar Boleto Bancário</span>
                        </label>
                        
                        <div class="ml-8">
                            <label for="data_limite_boleto" class="block font-medium text-sm text-gray-700 mb-1">Ocultar opção de boleto a partir do dia:</label>
                            <input type="date" name="data_limite_boleto" id="data_limite_boleto" value="{{ old('data_limite_boleto', $event->data_limite_boleto ? \Carbon\Carbon::parse($event->data_limite_boleto)->format('Y-m-d') : '') }}" class="w-full sm:w-auto border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black text-sm">
                            <p class="text-xs text-gray-500 mt-2">Deixe em branco se não quiser limite de data. Recomendado: 3 a 5 dias antes do evento.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end mt-8">
                <button type="submit" class="bg-black hover:bg-gray-800 text-white px-8 py-3 rounded-xl text-sm font-bold shadow-sm transition">
                    Salvar Alterações
                </button>
            </div>
        </form>

    </div>

    <!-- SCRIPT DO EDITOR DE TEXTO (TINYMCE) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init({
                selector: '#descricao_editor',
                plugins: 'advlist autolink lists link image media table code wordcount',
                toolbar: 'undo redo | formatselect | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link media | code',
                language: 'pt_BR', // Deixa os menus em português
                branding: false, // Tira a logo do TinyMCE do cantinho
                promotion: false,
                menubar: false,
                height: 400,
                // A linha abaixo é a mágica que permite você colar iframes sem o sistema apagar por segurança
                extended_valid_elements: 'iframe[src|width|height|name|align|frameborder|scrolling|allowfullscreen|allow]', 
            });
        });
    </script>

@endsection