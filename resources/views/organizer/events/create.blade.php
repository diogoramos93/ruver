@extends('layouts.organizer')

@section('title', 'Criar Novo Evento')

@section('content')

    <!-- Título da Página e Botão Voltar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Criar Novo Evento</h2>
        <a href="{{ route('organizer.events.index') }}" class="text-sm font-bold text-gray-500 hover:text-black transition">
            &larr; Voltar para Eventos
        </a>
    </div>

    <!-- FORMULÁRIO DE CRIAÇÃO -->
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100 p-6 sm:p-8">
        
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-medium shadow-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('organizer.events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Título e Categoria -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">Título do Evento *</label>
                    <input type="text" name="titulo" value="{{ old('titulo') }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">Categoria *</label>
                    <input type="text" name="categoria" value="{{ old('categoria') }}" placeholder="Ex: Corrida, Show, Conferência" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
            </div>

            <!-- CAMPO DE DESCRIÇÃO COM O EDITOR -->
            <div>
                <label class="block font-medium text-sm text-gray-700 mb-2">Descrição do Evento *</label>
                <textarea name="descricao" id="descricao_editor" rows="10" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">{{ old('descricao') }}</textarea>
            </div>

            <!-- Endereço, Cidade e Estado -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">Endereço *</label>
                    <input type="text" name="endereco" value="{{ old('endereco') }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">Cidade *</label>
                    <input type="text" name="cidade" value="{{ old('cidade') }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">Estado (Sigla) *</label>
                    <input type="text" name="estado" value="{{ old('estado') }}" maxlength="2" placeholder="Ex: SC" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
            </div>

            <!-- CEP e Datas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">CEP / Código Postal</label>
                    <input type="text" name="cep" value="{{ old('cep') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">Data de Início e Hora *</label>
                    <input type="datetime-local" name="data_inicio" value="{{ old('data_inicio') }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">Data de Término e Hora *</label>
                    <input type="datetime-local" name="data_termino" value="{{ old('data_termino') }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
            </div>

            <!-- Status e Política de Reembolso -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">Status do Evento *</label>
                    <select name="status" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                        <option value="ativo" {{ old('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                        <option value="rascunho" {{ old('status') == 'rascunho' ? 'selected' : '' }}>Rascunho</option>
                        <option value="encerrado" {{ old('status') == 'encerrado' ? 'selected' : '' }}>Encerrado</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">Política de Reembolso *</label>
                    <input type="text" name="politica_reembolso" value="{{ old('politica_reembolso') }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                </div>
            </div>

            <!-- Regulamentos -->
            <div>
                <label class="block font-medium text-sm text-gray-700 mb-1">Regulamentos *</label>
                <textarea name="regulamentos" rows="3" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">{{ old('regulamentos') }}</textarea>
            </div>

            <!-- Imagens e Banners -->        
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-200 pt-6 mt-6">
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">Banner do Evento <span class="text-gray-400 font-normal">(Opcional)</span></label>
                    <input type="file" name="banner" accept="image/jpeg,image/png" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition">
                    
                    <!-- NOVO: Checkbox de Destaque -->
                    <div class="mt-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <label class="flex items-center cursor-pointer">
                            <!-- Usamos o name exato da coluna no banco -->
                            <input type="checkbox" name="banner_destaque_organizador" value="1" class="rounded border-gray-300 text-black shadow-sm focus:ring-black h-5 w-5" {{ old('banner_destaque_organizador') ? 'checked' : '' }}>
                            <span class="ml-2 text-sm font-medium text-gray-900">Destacar no carrossel da página inicial</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-7">O banner principal do evento será exibido no topo do site.</p>
                    </div>
                </div>
                
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">Imagem do Card / Flyer <span class="text-gray-400 font-normal">(Opcional)</span></label>
                    <input type="file" name="imagem_card" accept="image/jpeg,image/png" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition">
                </div>
            </div>

            <!-- Documentos -->
            <div class="border-t border-gray-200 pt-6 mt-6">
                <label class="block font-medium text-sm text-gray-700 mb-1">Documentos da Prova / Regulamentos (PDF) <span class="text-gray-400 font-normal">(Opcional)</span></label>
                <input type="file" name="documentos[]" multiple accept="application/pdf" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition">
                <p class="text-xs text-gray-400 mt-2">Você pode selecionar múltiplos arquivos PDF segurando a tecla Ctrl ou Shift.</p>
            </div>

            <!-- MÉTODOS DE PAGAMENTO -->
            <div class="border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Métodos de Pagamento Permitidos</h3>
                
                <div class="space-y-4">
                    <!-- PIX -->
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="aceita_pix" value="1" class="rounded border-gray-300 text-black shadow-sm focus:ring-black h-5 w-5" {{ old('aceita_pix', true) ? 'checked' : '' }}>
                        <span class="ml-3 text-sm font-medium text-gray-700">Aceitar PIX</span>
                    </label>

                    <!-- Cartão de Crédito -->
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="aceita_cartao" value="1" class="rounded border-gray-300 text-black shadow-sm focus:ring-black h-5 w-5" {{ old('aceita_cartao', true) ? 'checked' : '' }}>
                        <span class="ml-3 text-sm font-medium text-gray-700">Aceitar Cartão de Crédito</span>
                    </label>

                    <!-- Boleto -->
                    <div class="p-5 bg-gray-50 rounded-xl border border-gray-200">
                        <label class="flex items-center cursor-pointer mb-4">
                            <input type="checkbox" name="aceita_boleto" value="1" class="rounded border-gray-300 text-black shadow-sm focus:ring-black h-5 w-5" {{ old('aceita_boleto', true) ? 'checked' : '' }}>
                            <span class="ml-3 text-sm font-bold text-gray-900">Aceitar Boleto Bancário</span>
                        </label>
                        
                        <div class="ml-8">
                            <label for="data_limite_boleto" class="block font-medium text-sm text-gray-700 mb-1">Ocultar opção de boleto a partir do dia:</label>
                            <input type="date" name="data_limite_boleto" id="data_limite_boleto" value="{{ old('data_limite_boleto') }}" class="w-full sm:w-auto border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black text-sm">
                            <p class="text-xs text-gray-500 mt-2">Deixe em branco se não quiser limite de data. Recomendado: 3 a 5 dias antes do evento.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botão Salvar -->
            <div class="flex items-center justify-end mt-8">
                <button type="submit" class="bg-black hover:bg-gray-800 text-white px-8 py-3 rounded-xl text-sm font-bold shadow-sm transition">
                    Cadastrar Evento Completo
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
                language: 'pt_BR',
                branding: false,
                promotion: false,
                menubar: false,
                height: 400,
                extended_valid_elements: 'iframe[src|width|height|name|align|frameborder|scrolling|allowfullscreen|allow]', 
            });
        });
    </script>

@endsection