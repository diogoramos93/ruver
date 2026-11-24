@extends('layouts.organizer')

@section('title', 'Configurações')

@section('content')

    <div class="mb-8">
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Configurações da Organização</h2>
        <p class="text-sm text-gray-500 mt-1">Gerencie seu perfil, identidade visual e métodos de pagamento.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-2xl relative mb-6 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl relative mb-6 text-sm font-medium">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('organizer.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- BLOCO 1: INFORMAÇÕES BÁSICAS -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Informações Básicas</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nome da Organização</label>
                    <input type="text" name="nome" value="{{ old('nome', $organizer->nome) }}" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Telefone / Celular</label>
                    <input type="text" name="telefone" value="{{ old('telefone', $organizer->telefone) }}" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Ano de Criação da Empresa</label>
                    <input type="text" name="ano_criacao" value="{{ old('ano_criacao', $organizer->ano_criacao) }}" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm" placeholder="Ex: 2021">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Biografia (Bio)</label>
                    <textarea name="bio" rows="4" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">{{ old('bio', $organizer->bio) }}</textarea>
                </div>
            </div>
        </div>

        <!-- BLOCO 2: REDES SOCIAIS -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Redes Sociais</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Instagram (Link completo)</label>
                    <input type="url" name="instagram" value="{{ old('instagram', $organizer->instagram) }}" placeholder="https://instagram.com/seu.perfil" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">WhatsApp (Apenas números com DDD)</label>
                    <input type="text" name="whatsapp" value="{{ old('whatsapp', $organizer->whatsapp) }}" placeholder="5511999999999" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Facebook (Link completo)</label>
                    <input type="url" name="facebook" value="{{ old('facebook', $organizer->facebook) }}" placeholder="https://facebook.com/suapagina" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">YouTube (Link completo)</label>
                    <input type="url" name="youtube" value="{{ old('youtube', $organizer->youtube) }}" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                </div>
            </div>
        </div>

        <!-- BLOCO 3: IDENTIDADE VISUAL E DOMÍNIO -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Identidade Visual e Domínio</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Cor Principal da Marca (Hexadecimal)</label>
                    <input type="color" name="cor_marca" value="{{ old('cor_marca', $organizer->cor_marca ?? '#000000') }}" class="h-10 w-full rounded-xl border-gray-300 cursor-pointer shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Domínio Personalizado (ex: eventos.suaempresa.com)</label>
                    <input type="text" name="dominio" value="{{ old('dominio', $organizer->dominio) }}" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm" placeholder="Sem http:// ou https://">
                    <p class="text-xs text-gray-500 mt-2">Lembre-se de apontar o CNAME ou IP (A) nas configurações de DNS do seu provedor.</p>
                </div>

                <!-- Logo da Empresa com Preview -->
                <div class="mt-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Logo da Empresa</label>
                    @if($organizer->logo)
                        <div class="mb-2 flex items-center space-x-3 p-2 bg-gray-50 rounded-xl border border-gray-200 w-fit">
                            <img src="{{ asset('storage/' . $organizer->logo) }}" alt="Logo Atual" class="h-10 w-auto object-contain">
                            
                        </div>
                    @endif
                    <input type="file" name="logo" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-black hover:file:bg-gray-200">
                </div>

                <!-- Ícone com Preview -->
                <div class="mt-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Ícone (Favicon)</label>
                    @if($organizer->icone)
                        <div class="mb-2 flex items-center space-x-3 p-2 bg-gray-50 rounded-xl border border-gray-200 w-fit">
                            <img src="{{ asset('storage/' . $organizer->icone) }}" alt="Ícone Atual" class="h-8 w-8 object-contain">
                            
                        </div>
                    @endif
                    <input type="file" name="icone" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-black hover:file:bg-gray-200">
                </div>
            </div>
        </div>

        <!-- BLOCO 4: MÉTODOS DE PAGAMENTO -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Métodos de Pagamento</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Mercado Pago -->
              <!--   <div class="p-5 border border-gray-200 rounded-2xl bg-gray-50/50">
                    <h4 class="font-bold text-blue-600 mb-4 flex items-center">
                        Mercado Pago
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Public Key</label>
                            <input type="text" name="mp_public_key" value="{{ old('mp_public_key', $organizer->mp_public_key) }}" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Access Token</label>
                            <input type="password" name="mp_access_token" value="{{ old('mp_access_token', $organizer->mp_access_token) }}" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                        </div>
                    </div>
                </div>
                -->

                <!-- Asaas -->
                <div class="p-5 border border-gray-200 rounded-2xl bg-gray-50/50">
                    <h4 class="font-bold text-green-600 mb-4 flex items-center">
                        ASAAS
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">API Key</label>
                            <input type="password" name="asaas_api_key" value="{{ old('asaas_api_key', $organizer->asaas_api_key) }}" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Wallet ID (Split)</label>
                            <input type="text" name="asaas_wallet_id" value="{{ old('asaas_wallet_id', $organizer->asaas_wallet_id) }}" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Token do Webhook</label>
                            <input type="password" name="asaas_webhook_token" value="{{ old('asaas_webhook_token', $organizer->asaas_webhook_token) }}" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm" placeholder="Cole aqui o token de autenticação">
                            <p class="text-xs text-gray-500 mt-1">Usado para receber atualizações automáticas de pagamento.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTÃO SALVAR -->
        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center px-8 py-3 bg-black hover:bg-gray-800 text-white font-bold rounded-xl shadow-md transition">
                Salvar Configurações
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 ml-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            </button>
        </div>

    </form>
@endsection