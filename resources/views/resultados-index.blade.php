<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        @php
            // Resgata o organizador injetado pelo Middleware na request (se houver domínio personalizado)
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

        <title>Resultados de Eventos | {{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white text-gray-900 font-sans antialiased flex flex-col min-h-screen"
          x-data="{ search: '' }">

<!-- Navbar -->
<nav class="shadow-md relative z-40" style="background-color: {{ $brandColor }};">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
        <!-- Logo Dinâmica (White-Label) -->
        <a href="{{ route('home') }}" class="flex items-center">
            <img src="{{ $brandLogo }}" alt="Logo" class="h-6 w-auto object-contain">
        </a>

        <!-- LINKS DO TOPO (Apenas Computador) -->
        <div class="hidden lg:flex items-center">
            
            <!-- Link Início (Casinha) -->
            <a href="{{ route('home') }}" class="flex items-center text-sm font-semibold text-gray-300 hover:text-white mr-6 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Início
            </a>

            <!-- Link Resultados -->
            <a href="{{ route('resultados.index') }}" class="flex items-center text-sm font-semibold text-gray-300 hover:text-white mr-6 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                </svg>
                Resultados
            </a>

            @auth
                <a href="{{ route('my.tickets') }}" class="flex items-center text-sm font-semibold text-gray-300 hover:text-white mr-6 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                    </svg>
                    Meus Ingressos
                </a>
                <a href="{{ route('dashboard') }}" class="flex items-center text-sm font-semibold text-gray-300 hover:text-white mr-6 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    Meu Painel
                </a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-300 hover:text-white transition">Entrar</a>
            @endauth
        </div>
    </div>
</nav>

        <!-- Conteúdo Principal -->
        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex-grow w-full">
            
            <div class="mb-8 pt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold uppercase tracking-tight text-gray-950">Resultados Oficiais</h1>
                    <p class="text-gray-500 mt-1 text-sm">Selecione um evento abaixo para consultar as classificações e os tempos oficiais.</p>
                </div>

                <!-- Botão de Busca Rápida no Topo -->
                <div class="relative w-full sm:w-72">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </span>
                    <input type="text" x-model="search" placeholder="Buscar evento por nome ou cidade..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-black focus:bg-white transition">
                </div>
            </div>

            <!-- Lista em formato de Linhas/Cards Horizontais -->
            <div class="space-y-4">
                @forelse($events as $event)
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-xl flex flex-col sm:flex-row items-stretch group"
                         x-show="search === '' || 
                                 @js(strtolower($event->titulo)).includes(search.toLowerCase()) || 
                                 @js(strtolower($event->cidade)).includes(search.toLowerCase())">
                        
                        <!-- Imagem do Card na Lateral Esquerda -->
                        <a href="{{ url('/evento/' . $event->slug . '/resultados') }}" class="w-full sm:w-48 h-48 sm:h-auto bg-gray-100 shrink-0 relative overflow-hidden block">
                            @if($event->imagem_card)
                                <img src="{{ asset('storage/' . $event->imagem_card) }}" alt="{{ $event->titulo }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs">Sem imagem</div>
                            @endif
                        </a>

                        <!-- Informações à Direita -->
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            
                            <!-- Bloco superior com a Caixinha Preta de Data e o Título -->
                            <div>
                                <div class="flex items-center space-x-4 mb-4">
                                    
                                    <!-- Caixinha da Data (Padrão original preto) -->
                                    <div class="flex flex-col items-center justify-center bg-black text-white rounded-xl px-3.5 py-2.5 border border-gray-900 text-center min-w-[60px] shrink-0 shadow-sm">
                                        <span class="text-xl font-extrabold leading-none">{{ \Carbon\Carbon::parse($event->data_inicio)->format('d') }}</span>
                                        <span class="text-xs font-bold uppercase tracking-wider mt-1 text-gray-200">{{ \Carbon\Carbon::parse($event->data_inicio)->translatedFormat('M') }}</span>
                                        <span class="text-[11px] font-bold text-white leading-none mt-1">{{ \Carbon\Carbon::parse($event->data_inicio)->format('Y') }}</span>
                                    </div>

                                    <!-- Cidade, Estado e Nome do Evento -->
                                    <div class="flex flex-col justify-center flex-1 min-w-0">
                                        <div class="flex items-center text-xs text-gray-500 font-medium mb-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 mr-1 shrink-0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                            <span class="truncate">{{ $event->cidade }}, {{ $event->estado }}</span>
                                        </div>

                                        <a href="{{ url('/evento/' . $event->slug . '/resultados') }}" class="block">
                                            <h3 class="text-base sm:text-lg font-bold text-gray-900 line-clamp-2 hover:text-indigo-600 transition leading-snug">
                                                {{ $event->titulo }}
                                            </h3>
                                        </a>
                                    </div>

                                </div>
                            </div>

                            <!-- Botão "Ver Resultados" Original com Ícone Verde -->
                            <div class="flex justify-end mt-2">
                                <a href="{{ url('/evento/' . $event->slug . '/resultados') }}" class="bg-black text-white text-xs font-semibold px-4 py-2.5 rounded-xl hover:bg-gray-800 transition flex items-center shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-2 text-[#39ff14]">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                                    </svg>
                                    Ver Resultados
                                </a>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="bg-white rounded-2xl border border-gray-200 text-center py-16 px-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-gray-300 mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <h3 class="text-base font-bold text-gray-700">Nenhum resultado disponível no momento</h3>
                        <p class="text-xs text-gray-500 mt-1">Assim que os arquivos de cronometragem forem adicionados, os eventos aparecerão aqui.</p>
                    </div>
                @endforelse
            </div>
            <!-- 👇 BOTÕES DE PAGINAÇÃO (INDEXAÇÃO) -->
            @if(method_exists($events, 'hasPages') && $events->hasPages())
                <div class="mt-8">
                    {{ $events->links() }}
                </div>
            @endif
        </main>

        <!-- MENU FLUTUANTE DE 5 ÍCONES (CELULAR) -->
        <div class="fixed bottom-4 left-4 right-4 z-50 lg:hidden">
            <div class="bg-[#2a2a2c] rounded-[2rem] px-6 py-1.5 flex justify-between items-center shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5">
                
                <!-- Ícone 1: Início -->
                <a href="{{ route('home') }}" class="w-12 h-12 flex items-center justify-center text-white/80 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                </a>

                <!-- Ícone 2: Lupa -->
                <a href="{{ route('home') }}#search-section" class="w-12 h-12 flex items-center justify-center text-white/80 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </a>

                <!-- Ícone 3: Troféu (Resultados - ATIVO COM VERDE NEON) -->
                <a href="{{ route('resultados.index') }}" class="w-12 h-12 flex items-center justify-center bg-[#39ff14] rounded-full text-black hover:scale-105 transition-transform shadow-[0_0_15px_rgba(57,255,20,0.3)]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                    </svg>
                </a>

                <!-- Ícone 4: Ingresso -->
                <a href="{{ Auth::check() ? route('my.tickets') : route('login') }}" class="w-12 h-12 flex items-center justify-center text-white/80 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                    </svg>
                </a>

                <!-- Ícone 5: Usuário -->
                <a href="{{ Auth::check() ? route('dashboard') : route('login') }}" class="w-12 h-12 flex items-center justify-center text-white/80 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </a>

            </div>
        </div>

        <footer class="bg-black text-gray-400 py-6 mt-12 border-t border-gray-900 text-xs pb-28 lg:pb-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-gray-300">&copy; {{ date('Y') }} IngressosApp. Todos os direitos reservados.</p>
            </div>
        </footer>

    </body>
</html>