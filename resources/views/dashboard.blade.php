<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        @php
            // Resgata o organizador injetado pelo Middleware na request
            $organizer = request()->get('currentOrganizer');

            // Define a logo: se o organizador tiver logo salva, usa ela; senão, usa a padrão da Ruver
            $brandLogo = ($organizer && $organizer->logo) 
                ? asset('storage/' . $organizer->logo) 
                : asset('images/1-Ruver Logo.svg');

            // Define a cor da barra (se o organizador tiver cor cadastrada, usa ela; senão, usa preto)
            $brandColor = ($organizer && $organizer->cor_marca) 
                ? $organizer->cor_marca 
                : '#000000';

            // Define o Favicon dinâmico do organizador ou usa o padrão da Ruver
            $brandIcon = ($organizer && $organizer->icone) 
                ? asset('storage/' . $organizer->icone) 
                : asset('images/favicon.png');
        @endphp

        <!-- Favicon Dinâmico (White-Label) -->
        <link rel="icon" type="image/png" href="{{ $brandIcon }}">

        <title>Meu Painel - {{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 text-gray-900 font-sans antialiased flex flex-col min-h-screen">

        <!-- Navbar Dinâmica -->
        <nav class="shadow-md relative z-40" style="background-color: {{ $brandColor }};">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
                <!-- Logo Dinâmica (White-Label) -->
                <a href="{{ route('home') }}" class="flex items-center">
                    <img src="{{ $brandLogo }}" alt="Logo" class="h-6 w-auto object-contain">
                </a>

                <!-- Escondido no mobile, pois temos a barra inferior -->
                <div class="hidden lg:flex items-center space-x-4">
                    <a href="{{ route('my.tickets') }}" class="text-sm font-semibold text-gray-300 hover:text-white transition">Meus Ingressos</a>
                    <!-- NOVO: Link de Favoritos na Navbar -->
                    <a href="{{ route('my.favorites') }}" class="text-sm font-semibold text-gray-300 hover:text-white transition">Meus Favoritos</a>
                    <a href="{{ route('home') }}" class="text-sm font-semibold text-gray-300 hover:text-white transition">← Início</a>
                </div>
            </div>
        </nav>

        <!-- Conteúdo Principal do Painel -->
        <main class="max-w-5xl mx-auto px-4 py-10 flex-grow w-full">
            
            <div class="mb-10">
                <h1 class="text-2xl font-bold text-gray-900">Olá, {{ auth()->user()->name }}! 👋</h1>
                <p class="text-sm text-gray-500 mt-1">Bem-vindo à sua central de cliente. O que você deseja acessar?</p>
            </div>

            <!-- Grid de Atalhos (Ajustado para 3 colunas em telas grandes) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Atalho: Meus Ingressos -->
                <a href="{{ route('my.tickets') }}" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 hover:shadow-md hover:border-gray-300 transition group flex flex-col items-start">
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
                        🎟️
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 group-hover:text-black transition">Meus Ingressos</h2>
                    <p class="text-sm text-gray-500 mt-1">Acesse seus QR Codes, visualize e gerencie suas compras de eventos.</p>
                </a>

                <!-- NOVO: Atalho Meus Favoritos -->
                <a href="{{ route('my.favorites') }}" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 hover:shadow-md hover:border-gray-300 transition group flex flex-col items-start">
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
                        ❤️
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 group-hover:text-black transition">Meus Favoritos</h2>
                    <p class="text-sm text-gray-500 mt-1">Veja a lista de todos os eventos que você curtiu e salvou para depois.</p>
                </a>

                <!-- Atalho: Configurações / Perfil -->
                <a href="{{ route('profile.edit') }}" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 hover:shadow-md hover:border-gray-300 transition group flex flex-col items-start">
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
                        ⚙️
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 group-hover:text-black transition">Configurações da Conta</h2>
                    <p class="text-sm text-gray-500 mt-1">Atualize seus dados pessoais, endereço, e-mail e senha de acesso.</p>
                </a>

            </div>
        
            <!-- Botão de Sair -->
            <div class="mt-10 border-t border-gray-200 pt-8">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-black hover:bg-gray-800 text-white text-sm font-bold rounded-xl shadow-sm transition">
                        🚪 Sair
                    </button>
                </form>
            </div>
        </main>

        <!-- MENU FLUTUANTE DE 5 ÍCONES (APENAS CELULAR) -->
        <div class="fixed bottom-4 left-4 right-4 z-50 lg:hidden">
            <div class="bg-[#2a2a2c] rounded-[2rem] px-6 py-1.5 flex justify-between items-center shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5">
                
                <!-- Ícone 1: Início -->
                <a href="{{ route('home') }}" class="w-12 h-12 flex items-center justify-center text-white/80 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                </a>

                <!-- Ícone 2: Lupa (Busca) -->
                <a href="{{ route('home') }}#search-section" class="w-12 h-12 flex items-center justify-center text-white/80 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </a>

                <!-- Ícone 3: Troféu (Resultados) -->
                <a href="{{ route('resultados.index') }}" class="w-12 h-12 flex items-center justify-center text-white/80 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                    </svg>
                </a>

                <!-- Ícone 4: Ingresso (Meus Ingressos) -->
                <a href="{{ route('my.tickets') }}" class="w-12 h-12 flex items-center justify-center text-white/80 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                    </svg>
                </a>

                <!-- Ícone 5: Usuário (ATIVO NESTA PÁGINA) -->
                <a href="{{ route('dashboard') }}" class="w-12 h-12 flex items-center justify-center bg-[#39ff14] rounded-full text-black hover:scale-105 transition-transform shadow-[0_0_15px_rgba(57,255,20,0.3)]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </a>

            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-black text-gray-400 py-6 mt-12 border-t border-gray-900 text-xs pb-28 lg:pb-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-gray-300">&copy; {{ date('Y') }} IngressosApp. Todos os direitos reservados.</p>
            </div>
        </footer>

    </body>
</html>