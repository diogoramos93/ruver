<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Organizador - Super Admin</title>
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
            </div>
            <a href="{{ route('admin.organizers.index') }}" class="text-xs font-bold text-gray-300 hover:text-white transition">
                &larr; Voltar para a lista
            </a>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full space-y-8">

        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Editar Organizador: {{ $organizer->nome }}</h1>
            <p class="text-xs text-gray-500 mt-1">Atualize as configurações da conta, status de acesso e taxas da plataforma</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-xs font-semibold">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white border border-gray-100 shadow-xl sm:rounded-3xl p-6 sm:p-8">
            <form action="{{ route('admin.organizers.update', $organizer->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- DADOS BÁSICOS DA CONTA -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 pb-2 mb-4">Dados Principais & Acesso</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Status da Conta *</label>
                            <select name="ativo" required class="w-full bg-white border-gray-300 rounded-xl shadow-sm focus:ring-black focus:border-black text-sm p-3 font-bold">
                                <option value="1" {{ old('ativo', $organizer->ativo) == 1 ? 'selected' : '' }}>Ativo (Acesso Liberado)</option>
                                <option value="0" {{ old('ativo', $organizer->ativo) == 0 ? 'selected' : '' }}>Inativo (Acesso Bloqueado)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nome do Organizador / Empresa *</label>
                            <input type="text" name="nome" value="{{ old('nome', $organizer->nome) }}" required class="w-full bg-white border-gray-300 rounded-xl shadow-sm focus:ring-black focus:border-black text-sm p-3">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">CNPJ (Opcional)</label>
                            <input type="text" name="cnpj" value="{{ old('cnpj', $organizer->cnpj) }}" placeholder="00.000.000/0001-00" class="w-full bg-white border-gray-300 rounded-xl shadow-sm focus:ring-black focus:border-black text-sm p-3">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">E-mail de Login *</label>
                            <input type="email" name="email" value="{{ old('email', $organizer->email) }}" required class="w-full bg-white border-gray-300 rounded-xl shadow-sm focus:ring-black focus:border-black text-sm p-3">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Redefinir Senha (Deixe em branco para manter a atual)</label>
                            <input type="password" name="password" placeholder="Nova senha (opcional)" class="w-full bg-white border-gray-300 rounded-xl shadow-sm focus:ring-black focus:border-black text-sm p-3">
                        </div>
                    </div>
                </div>

                <!-- CONFIGURAÇÃO DA TAXA DA PLATAFORMA -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 pb-2 mb-4">Taxa da Plataforma (Super Admin)</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50/80 p-5 rounded-2xl border border-gray-200">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tipo de Cobrança da Taxa *</label>
                            <select name="tipo_taxa_plataforma" required class="w-full bg-white border-gray-300 rounded-xl shadow-sm focus:ring-black focus:border-black text-sm p-3 font-semibold">
                                <option value="porcentagem" {{ old('tipo_taxa_plataforma', $organizer->tipo_taxa_plataforma) === 'porcentagem' ? 'selected' : '' }}>Porcentagem (%) por Venda</option>
                                <option value="fixo" {{ old('tipo_taxa_plataforma', $organizer->tipo_taxa_plataforma) === 'fixo' ? 'selected' : '' }}>Valor Fixo (R$) por Ingresso</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Valor da Taxa *</label>
                            <input type="number" step="0.01" min="0" name="taxa_plataforma" value="{{ old('taxa_plataforma', $organizer->taxa_plataforma ?? '10.00') }}" required class="w-full bg-white border-gray-300 rounded-xl shadow-sm focus:ring-black focus:border-black text-sm p-3 font-mono font-bold text-emerald-600">
                            <p class="text-[11px] text-gray-500 mt-1">Ex: 10 para 10% ou 3.50 para R$ 3,50 por ingresso vendido.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.organizers.index') }}" class="text-xs font-bold text-gray-500 hover:text-gray-800 px-4 py-2.5 transition">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-black hover:bg-gray-800 text-white font-bold text-xs px-6 py-3 rounded-xl transition shadow-md">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>

    </main>

</body>
</html>
