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

        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- Alpine.js para o Carrossel funcionar de forma fluida -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="bg-white text-gray-900 font-sans antialiased flex flex-col min-h-screen">

<!-- Navbar Dinâmica -->
<nav class="shadow-md relative z-40" style="background-color: {{ $brandColor }};">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
<!-- Logo Dinâmica (White-Label) -->
    <a href="{{ route('home') }}" class="flex items-center">
        <img src="{{ $brandLogo }}" alt="Logo" class="{{ ($organizer && $organizer->logo) ? 'h-6 md:h-8 max-h-10' : 'h-6' }} w-auto object-contain">
    </a>

        <!-- LINKS DO TOPO (Apenas Computador) -->
        <div class="hidden lg:flex items-center">
            
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

        <!-- Carrossel de Banners dos Eventos -->
        @php
            // Proteção: transforma $events em Collection se for um objeto de paginação
            $eventList = $events instanceof \Illuminate\Pagination\AbstractPaginator ? $events->getCollection() : $events;
            
            // Filtra eventos com banner e destaque, e reseta as chaves da lista com ->values()
            $bannerEvents = $eventList->filter(function($event) {
                return !empty($event->banner) && $event->banner_destaque_organizador == 1;
            })->values();
        @endphp

        @if($bannerEvents->count() > 0)
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 w-full relative z-10">
                <div x-data="{ 
                        activeSlide: 1, 
                        totalSlides: {{ $bannerEvents->count() }},
                        init() {
                            setInterval(() => {
                                this.activeSlide = this.activeSlide === this.totalSlides ? 1 : this.activeSlide + 1;
                            }, 5000);
                        }
                    }" 
                    class="relative w-full rounded-2xl overflow-hidden shadow-lg bg-gray-900 aspect-[3/1] max-h-[400px]">
                    
                    <!-- Slides -->
                    <div class="relative w-full h-full">
                        @foreach($bannerEvents as $index => $event)
                            <div x-show="activeSlide === {{ $index + 1 }}" 
                                 x-transition:enter="transition ease-out duration-500"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 class="absolute inset-0 w-full h-full">
                                
                                <!-- Link clicável que leva direto para a página do evento -->
                                <a href="{{ route('event.show', $event->slug ?? $event->id) }}" class="block w-full h-full relative group">
                                    <img src="{{ asset('storage/' . $event->banner) }}" alt="{{ $event->titulo }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <!-- Botões de Navegação (Anterior / Próximo) -->
                    <button @click="activeSlide = activeSlide === 1 ? totalSlides : activeSlide - 1" class="absolute left-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/80 text-white p-2 rounded-full transition focus:outline-none z-10">
                        ❮
                    </button>
                    <button @click="activeSlide = activeSlide === totalSlides ? 1 : activeSlide + 1" class="absolute right-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/80 text-white p-2 rounded-full transition focus:outline-none z-10">
                        ❯
                    </button>

                    <!-- Indicadores (Bolinhas) -->
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2 z-10">
                        @foreach($bannerEvents as $index => $event)
                            <button @click="activeSlide = {{ $index + 1 }}" :class="{ 'bg-white w-6': activeSlide === {{ $index + 1 }}, 'bg-white/50 w-2.5': activeSlide !== {{ $index + 1 }} }" class="h-2.5 rounded-full transition-all duration-300"></button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Lista de Eventos -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex-grow w-full">
            
            <!-- Adicionado id="search-section" para a âncora do menu mobile -->
            <!-- Cabeçalho e Barra de Filtro / Busca -->
            <div id="search-section" class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4 pt-4">
                <h2 class="text-2xl font-bold">Próximos Eventos</h2>

                <!-- Formulário de Filtro -->
                <form action="{{ route('home') }}" method="GET" class="flex items-center gap-2 w-full md:w-auto">
                    <div class="relative flex-1 md:w-72">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nome ou cidade..." class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-black focus:bg-white transition">
                    </div>
                    <button type="submit" class="bg-black text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-gray-800 transition shrink-0">
                        Buscar
                    </button>
                    @if(request('search'))
                        <a href="{{ route('home') }}" class="text-xs text-gray-500 hover:text-black underline shrink-0">Limpar</a>
                    @endif
                </form>
            </div>

            <!-- Grid de Cards com ID "events-grid" para o Scroll Infinito funcionar -->
            <div id="events-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                @forelse($events as $event)
                    <!-- Card de Evento -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl flex flex-col group relative">
                        
                        <!-- NOVO: BOTÃO DE FAVORITAR NO CANTO DA IMAGEM -->
                        <div class="absolute top-2 right-2 z-20">
                            @auth
                                <form action="{{ route('favoritos.toggle', $event->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-white/80 backdrop-blur-sm hover:bg-white p-2 rounded-full shadow-sm transition transform hover:scale-110">
                                        @if(in_array($event->id, $favoritosIds ?? []))
                                            <!-- Coração Preenchido (Vermelho) -->
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-red-500">
                                                <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                            </svg>
                                        @else
                                            <!-- Coração Vazio (Cinza) -->
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                            </svg>
                                        @endif
                                    </button>
                                </form>
                            @else
                                <!-- Se não estiver logado, leva pro login -->
                                <a href="{{ route('login') }}" class="bg-white/80 backdrop-blur-sm hover:bg-white p-2 rounded-full shadow-sm transition transform hover:scale-110 block">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                    </svg>
                                </a>
                            @endauth
                        </div>
                        
                            <!-- Imagem do Card / Flyer -->
                            <a href="{{ route('event.show', $event->slug ?? $event->id) }}" class="w-full aspect-square bg-gray-200 overflow-hidden relative block">
                                @if($event->imagem_card)
                                    <img src="{{ asset('storage/' . $event->imagem_card) }}" alt="{{ $event->titulo }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">Sem imagem</div>
                                @endif
                                {{-- BADGE "ENCERRADO" --}}
                                @if($event->ja_passou)
                                    <div class="absolute top-2 left-2 z-10">
                                        <span class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-xs font-bold rounded-full shadow-lg">
                                            Encerrado
                                        </span>
                                    </div>
                                @endif
                            </a>

                        <!-- Conteúdo do Card -->
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            
                            <!-- Bloco com Caixinha de Data na Esquerda e Endereço/Título na Direita Centralizados -->
                            <div>
                                <div class="flex items-center space-x-3 mb-2">
                                    
                                    <!-- Caixinha da Data -->
                                    <div class="flex flex-col items-center justify-center bg-black text-white rounded-lg px-3.5 py-2 border border-gray-900 text-center min-w-[56px] shrink-0">
                                        <span class="text-lg font-extrabold leading-none">{{ \Carbon\Carbon::parse($event->data_inicio)->format('d') }}</span>
                                        <span class="text-xs font-bold uppercase tracking-wider mt-1 text-gray-200">{{ \Carbon\Carbon::parse($event->data_inicio)->translatedFormat('M') }}</span>
                                        <span class="text-[12px] font-bold text-white leading-none mt-1">{{ \Carbon\Carbon::parse($event->data_inicio)->format('Y') }}</span>
                                    </div>

                                    <!-- Endereço e Nome do Evento -->
                                    <div class="flex flex-col justify-center flex-1 min-w-0">
                                        <div class="flex items-center text-xs text-gray-500 font-medium mb-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 mr-1 shrink-0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                            <span class="truncate">{{ $event->cidade }}, {{ $event->estado }}</span>
                                        </div>

                                        <a href="{{ route('event.show', $event->slug ?? $event->id) }}" class="block">
                                            <h3 class="text-sm font-bold text-gray-900 line-clamp-2 hover:text-indigo-600 transition leading-snug">
                                                {{ $event->titulo }}
                                            </h3>
                                        </a>
                                    </div>

                                </div>
                            </div>

                            <!-- Botão Ver Evento -->
                            <div class="mt-4 flex justify-end">
                                <a href="{{ route('event.show', $event->slug ?? $event->id) }}" class="bg-black text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                                    Ver evento
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12 text-gray-500">
                        Nenhum evento encontrado.
                    </div>
                @endforelse
            </div>

            <!-- Loader e âncora do Scroll Infinito -->
            @if(method_exists($events, 'hasMorePages') && $events->hasMorePages())
                <div id="loading-indicator" class="w-full flex justify-center items-center py-10 mt-4">
                    <svg class="animate-spin h-6 w-6 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span class="ml-2 text-sm text-gray-600 font-medium">Carregando mais eventos...</span>
                </div>
                <!-- Armazena a URL da próxima página do Laravel secretamente -->
                <input type="hidden" id="next-page-url" value="{{ $events->nextPageUrl() }}">
            @endif

        </main>

        <!-- MENU FLUTUANTE DE 5 ÍCONES (APENAS CELULAR) -->
        <div class="fixed bottom-4 left-4 right-4 z-50 lg:hidden">
            <div class="bg-[#2a2a2c] rounded-[2rem] px-6 py-1.5 flex justify-between items-center shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5">
                
                <!-- Ícone 1: Início (Ativo - Círculo Verde Neon) -->
                <a href="{{ route('home') }}" class="w-12 h-12 flex items-center justify-center bg-[#39ff14] rounded-full text-black hover:scale-105 transition-transform shadow-[0_0_15px_rgba(57,255,20,0.3)]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                </a>

                <!-- Ícone 2: Lupa (Busca) -->
                <a href="#search-section" class="w-12 h-12 flex items-center justify-center text-white/80 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </a>

                <!-- Ícone 3: Troféu (Ranking) -->
                 <a href="{{ route('resultados.index') }}" class="w-12 h-12 flex items-center justify-center text-white/80 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
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

        <!-- Footer -->
        <footer class="bg-black text-gray-400 py-6 mt-12 border-t border-gray-900 text-xs pb-28 lg:pb-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-gray-300">&copy; {{ date('Y') }} IngressosApp. Todos os direitos reservados.</p>
            </div>
        </footer>

        <!-- Javascript do Scroll Infinito -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const loadingIndicator = document.getElementById('loading-indicator');
                if (!loadingIndicator) return;

                const observer = new IntersectionObserver(async (entries) => {
                    // Quando o indicador entra na tela
                    if (entries[0].isIntersecting) {
                        const nextUrlInput = document.getElementById('next-page-url');
                        if (!nextUrlInput || !nextUrlInput.value) return;

                        const url = nextUrlInput.value;

                        // Pausa a observação para não duplicar requisições
                        observer.unobserve(loadingIndicator);

                        try {
                            // Busca a próxima página usando requisição silenciosa (Fetch)
                            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                            const html = await response.text();

                            // Converte a string de resposta para estrutura HTML virtual
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');

                            // Extrai apenas os novos cards de evento
                            const newGridItems = doc.getElementById('events-grid').innerHTML;
                            document.getElementById('events-grid').insertAdjacentHTML('beforeend', newGridItems);

                            // Atualiza a URL da nova "próxima página"
                            const newUrlInput = doc.getElementById('next-page-url');
                            if (newUrlInput && newUrlInput.value) {
                                nextUrlInput.value = newUrlInput.value;
                                observer.observe(loadingIndicator); // Volta a monitorar a descida
                            } else {
                                // Chegou ao fim de todos os eventos, então remove o indicador
                                loadingIndicator.remove();
                                nextUrlInput.remove();
                            }
                        } catch (error) {
                            console.error('Falha ao carregar mais eventos:', error);
                        }
                    }
                }, {
                    rootMargin: '300px' // Aciona o gatilho 300px ANTES do indicador aparecer na tela (fica mais fluído)
                });

                observer.observe(loadingIndicator);
            });
        </script>
    </body>
</html>