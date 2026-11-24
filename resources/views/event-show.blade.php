<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    @php
        $isFavorito = false;
        if(auth()->check()) {
            $isFavorito = auth()->user()->favoritos()->where('event_id', $event->id)->exists();
        }

        // Pega o organizador vinculado diretamente ao evento
        $eventOrganizer = request()->get('currentOrganizer');

        // Define a logo: se o organizador tiver logo salva, usa ela; senão, usa a padrão da Ruver
        $brandLogo = ($eventOrganizer && $eventOrganizer->logo) 
            ? asset('storage/' . $eventOrganizer->logo) 
            : asset('images/1-Ruver Logo.svg');

        // Define a cor da barra (se o organizador tiver cor cadastrada, usa ela; senão, usa preto)
        $brandColor = ($eventOrganizer && $eventOrganizer->cor_marca) 
            ? $eventOrganizer->cor_marca 
            : '#000000';

        // Define o Favicon dinâmico do organizador ou usa o padrão da Ruver
        $brandIcon = ($eventOrganizer && $eventOrganizer->icone) 
            ? asset('storage/' . $eventOrganizer->icone) 
            : asset('images/favicon.png');
    @endphp

    <!-- Favicon Dinâmico (White-Label) -->
    <link rel="icon" type="image/png" href="{{ $brandIcon }}">

    <title>{{ $event->titulo }} - {{ config('app.name', 'IngressosApp') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; } @media (max-width: 1023px) { .ruver-desktop-modal { display: none !important; } }</style>
    <!-- CSS do Leaflet (Para o Mapa) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body x-data="{ showMobileModal: false }" 
      :class="showMobileModal ? 'overflow-hidden' : ''"
      class="bg-gray-50 text-gray-900 font-sans antialiased flex flex-col min-h-screen">

<!-- Navbar Dinâmica -->
<nav class="shadow-md relative z-40" style="background-color: {{ $brandColor }};">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
        <!-- Logo Dinâmica (White-Label) -->
        <a href="{{ route('home') }}" class="flex items-center">
            <img src="{{ $brandLogo }}" alt="Logo" class="h-6 w-auto object-contain">
        </a>

        <!-- LINKS DO TOPO -->
        <div class="hidden lg:flex items-center">
            <a href="{{ route('home') }}" class="flex items-center text-sm font-semibold text-gray-300 hover:text-white mr-6 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Início
            </a>
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
                <a href="{{ url('/painel') }}" class="flex items-center text-sm font-semibold text-gray-300 hover:text-white mr-6 transition">
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
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full">
        
        <!-- Exibição das Mensagens de Sucesso -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-center font-bold">
                {{ session('success') }}
            </div>
        @endif

        <div class="lg:grid lg:grid-cols-12 lg:gap-10">
            
            <!-- COLUNA ESQUERDA -->
            <div class="lg:col-span-8 space-y-8">
                
                <!-- Imagem do Evento / Banner -->
                <div class="w-full aspect-video bg-black rounded-3xl shadow-xl relative flex items-center justify-center overflow-hidden">
                    
                    <!-- BOTÃO DE FAVORITAR FLUTUANTE -->
                    <div class="absolute top-4 right-4 z-30">
                        @auth
                            <form action="{{ route('favoritos.toggle', $event->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-white/80 backdrop-blur-sm hover:bg-white p-3 rounded-full shadow-lg transition transform hover:scale-110">
                                    @if($isFavorito)
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-red-500">
                                            <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-500">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                        </svg>
                                    @endif
                                </button>
                            </form>
                        @else
                            <!-- 👇 CORREÇÃO: AGORA ELE USA A ROTA MÁGICA 👇 -->
                            <a href="{{ route('favoritos.add', $event->id) }}" class="bg-white/80 backdrop-blur-sm hover:bg-white p-3 rounded-full shadow-lg transition transform hover:scale-110 block">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-500">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                </svg>
                            </a>
                        @endauth
                    </div>
                    {{-- BADGE "ENCERRADO" — selo no canto superior esquerdo da imagem --}}
                    @if($event->ja_passou)
                        <div class="absolute top-4 left-4 z-30">
                            <span class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-xs font-bold rounded-full shadow-lg">
                                Encerrado
                            </span>
                        </div>
                    @endif
                    @if($event->banner || $event->imagem_card)
                        <div class="absolute inset-0 rounded-3xl overflow-hidden">
                            <img src="{{ asset('storage/' . ($event->banner ?? $event->imagem_card)) }}" alt="" class="w-full h-full object-cover filter blur-sm scale-110 opacity-75">
                            <div class="absolute inset-0 bg-black/40"></div>
                        </div>

                        <div class="relative z-10 h-4/5 aspect-square max-h-[85%] rounded-2xl shadow-2xl overflow-hidden border border-white/10 bg-black/40 flex items-center justify-center">
                            <img src="{{ asset('storage/' . ($event->imagem_card ?? $event->banner)) }}" alt="{{ $event->titulo }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="relative z-10 flex flex-col items-center justify-center text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mb-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a2.25 2.25 0 0 0 2.25-2.25V6a2.25 2.25 0 0 0-2.25-2.25H3.75A2.25 2.25 0 0 0 1.5 6v12a2.25 2.25 0 0 0 2.25 2.25Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                            <span class="text-xs font-bold tracking-wider">SEM IMAGEM</span>
                        </div>
                    @endif
                </div>

                <!-- Título do Evento -->
                <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 tracking-tight mb-6 text-center">
                    {{ $event->titulo }}
                </h1>
                <!-- Cabeçalho (Cronômetro, Título e Local) -->
                <div>
                    <!-- CRONÔMETRO -->
                    <div x-data="countdown('{{ $event->data_termino }}')" x-init="start()" class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm mb-6">
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4 text-center">
                            Este evento começa em:
                        </p>
                        
                        <div class="flex flex-wrap justify-center gap-4">
                            <div class="flex flex-col items-center">
                                <div class="relative w-16 h-16 flex items-center justify-center">
                                    <svg class="w-full h-full transform -rotate-90">
                                        <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" class="text-gray-200" fill="transparent"/>
                                        <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" class="text-black" fill="transparent" :stroke-dasharray="175.9" :stroke-dashoffset="175.9 - (175.9 * days / 30)"/>
                                    </svg>
                                    <span class="absolute text-lg font-extrabold text-gray-900" x-text="days">00</span>
                                </div>
                                <span class="text-[10px] font-bold tracking-widest text-gray-500 mt-1">DIAS</span>
                            </div>

                            <div class="flex flex-col items-center">
                                <div class="relative w-16 h-16 flex items-center justify-center">
                                    <svg class="w-full h-full transform -rotate-90">
                                        <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" class="text-gray-200" fill="transparent"/>
                                        <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" class="text-black" fill="transparent" :stroke-dasharray="175.9" :stroke-dashoffset="175.9 - (175.9 * hours / 24)"/>
                                    </svg>
                                    <span class="absolute text-lg font-extrabold text-gray-900" x-text="hours">00</span>
                                </div>
                                <span class="text-[10px] font-bold tracking-widest text-gray-500 mt-1">HORAS</span>
                            </div>

                            <div class="flex flex-col items-center">
                                <div class="relative w-16 h-16 flex items-center justify-center">
                                    <svg class="w-full h-full transform -rotate-90">
                                        <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" class="text-gray-200" fill="transparent"/>
                                        <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" class="text-black" fill="transparent" :stroke-dasharray="175.9" :stroke-dashoffset="175.9 - (175.9 * minutes / 60)"/>
                                    </svg>
                                    <span class="absolute text-lg font-extrabold text-gray-900" x-text="minutes">00</span>
                                </div>
                                <span class="text-[10px] font-bold tracking-widest text-gray-500 mt-1">MINS</span>
                            </div>

                            <div class="flex flex-col items-center">
                                <div class="relative w-16 h-16 flex items-center justify-center">
                                    <svg class="w-full h-full transform -rotate-90">
                                        <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" class="text-gray-200" fill="transparent"/>
                                        <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" class="text-black" fill="transparent" :stroke-dasharray="175.9" :stroke-dashoffset="175.9 - (175.9 * seconds / 60)"/>
                                    </svg>
                                    <span class="absolute text-lg font-extrabold text-gray-900" x-text="seconds">00</span>
                                </div>
                                <span class="text-[10px] font-bold tracking-widest text-gray-500 mt-1">SEGS</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bloco de Data e Local -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-center sm:space-x-8 space-y-4 sm:space-y-0 text-gray-600 mb-6">
                        <div class="flex items-start">
                            <div class="p-2 bg-gray-100 rounded-lg mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-black">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Data e Hora</p>
                                <p class="text-sm">{{ \Carbon\Carbon::parse($event->data_inicio)->format('d/m/Y') }} às {{ \Carbon\Carbon::parse($event->data_inicio)->format('H:i') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="p-2 bg-gray-100 rounded-lg mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-black">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Localização</p>
                                <p class="text-sm">{{ $event->cidade }} - {{ $event->estado }} @if($event->endereco) - {{ $event->endereco }} @endif</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Descrição do Evento (RENDERIZANDO HTML do TinyMCE) -->
                <div class="pt-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">SOBRE O EVENTO</h3>
                    
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm" x-data="{ open: false }">
                        <div class="relative">
                            <div :class="open ? '' : 'line-clamp-4'" class="prose prose-gray max-w-none text-gray-600 text-base leading-relaxed">
                                {!! $event->descricao !!}
                            </div>
                            
                            <div x-show="!open" class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
                        </div>

                        <!-- Botão Leia Mais -->
                        <div class="flex justify-center mt-6">
                            <button @click="open = !open" class="px-6 py-2 rounded-full border border-gray-300 text-gray-700 text-sm font-semibold hover:border-black hover:text-black transition-all duration-200 focus:outline-none flex items-center space-x-2">
                                <span x-text="open ? 'Ler menos' : 'Leia mais'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SEÇÕES EXPANSÍVEIS -->
                <div class="space-y-4 pt-2">
                    
                    <!-- Participantes -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ expanded: false }">
                        <button @click="expanded = !expanded" class="w-full px-6 py-4 flex items-center justify-between text-left focus:outline-none hover:bg-gray-50/50 transition">
                            <span class="font-extrabold text-sm sm:text-base text-gray-900 tracking-wider">PARTICIPANTES</span>
                            <span class="w-7 h-7 flex items-center justify-center rounded-full border border-gray-200 text-gray-600 text-lg font-bold transition-transform duration-200" x-text="expanded ? '-' : '+'"></span>
                        </button>
                        
                        <div x-show="expanded" class="px-6 pb-6 pt-4 border-t border-gray-100 flex items-center">
                            @php
                                $ticketIds = $event->tickets->pluck('id');
                                $participantesData = \App\Models\OrderParticipant::join('orders', 'order_participants.order_id', '=', 'orders.id')
                                    ->whereIn('order_participants.ticket_id', $ticketIds)
                                    ->where('orders.status', 'pago')
                                    ->select('order_participants.nome')
                                    ->get();
                                
                                $totalParticipantes = $participantesData->count();
                                $primeirosParticipantes = $participantesData->take(4);
                            @endphp

                            @if($totalParticipantes === 0)
                                <p class="text-sm text-gray-500 font-medium italic py-2">Seja o primeiro a garantir sua vaga!</p>
                            @else
                                <div class="flex -space-x-3">
                                    @foreach($primeirosParticipantes as $index => $participante)
                                        @php
                                            $nomeArr = explode(' ', trim($participante->nome));
                                            if (count($nomeArr) >= 2) {
                                                $iniciais = mb_substr($nomeArr[0], 0, 1) . mb_substr(end($nomeArr), 0, 1);
                                            } else {
                                                $iniciais = mb_substr($participante->nome, 0, 2);
                                            }
                                            $iniciais = mb_strtoupper($iniciais);
                                            $zIndex = $index + 1;
                                        @endphp
                                        <div class="w-10 h-10 rounded-full border-2 border-white bg-gray-500 text-white font-bold flex items-center justify-center relative z-[{{ $zIndex }}] text-sm shrink-0 shadow-sm" title="{{ $participante->nome }}">
                                            {{ $iniciais }}
                                        </div>
                                    @endforeach
                                    @if($totalParticipantes > 4)
                                        <div class="w-10 h-10 rounded-full border-2 border-white bg-gray-800 text-white font-bold flex items-center justify-center relative z-[5] text-sm shrink-0 shadow-sm">
                                            +{{ $totalParticipantes - 4 }}
                                        </div>
                                    @endif
                                </div>
                                <p class="ml-4 text-sm text-gray-600 font-medium">já garantiram vaga!</p>
                            @endif
                        </div>
                    </div>

                    <!-- Modalidades -->
                    @php
                        $modalidades = $event->ticketCategories ?? \App\Models\TicketCategory::where('event_id', $event->id)->get();
                    @endphp

                    @if($modalidades->count() > 0)
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ expanded: false }">
                        <button @click="expanded = !expanded" class="w-full px-6 py-4 flex items-center justify-between text-left focus:outline-none hover:bg-gray-50/50 transition">
                            <span class="font-extrabold text-sm sm:text-base text-gray-900 tracking-wider">MODALIDADES</span>
                            <span class="w-7 h-7 flex items-center justify-center rounded-full border border-gray-200 text-gray-600 text-lg font-bold transition-transform duration-200" x-text="expanded ? '-' : '+'"></span>
                        </button>
                        <div x-show="expanded" class="px-6 pb-6 text-gray-600 text-sm leading-relaxed border-t border-gray-100 pt-4">
                            <ul class="space-y-2">
                                @foreach($modalidades as $categoria)
                                    <li class="font-medium text-gray-800 flex items-center">
                                        <span class="w-2 h-2 bg-black rounded-full mr-2"></span>
                                        {{ $categoria->nome }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    <!-- Regulamento -->
                    @if(!empty($event->regulamentos))
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ expanded: false }">
                        <button @click="expanded = !expanded" class="w-full px-6 py-4 flex items-center justify-between text-left focus:outline-none hover:bg-gray-50/50 transition">
                            <span class="font-extrabold text-sm sm:text-base text-gray-900 tracking-wider">REGULAMENTO</span>
                            <span class="w-7 h-7 flex items-center justify-center rounded-full border border-gray-200 text-gray-600 text-lg font-bold transition-transform duration-200" x-text="expanded ? '-' : '+'"></span>
                        </button>
                        <div x-show="expanded" class="px-6 pb-6 text-gray-600 text-sm leading-relaxed border-t border-gray-100 pt-4 whitespace-pre-line">
                            {{ $event->regulamentos }}
                        </div>
                    </div>
                    @endif

                    <!-- Política de Reembolso -->
                    @if(!empty($event->politica_reembolso))
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ expanded: false }">
                        <button @click="expanded = !expanded" class="w-full px-6 py-4 flex items-center justify-between text-left focus:outline-none hover:bg-gray-50/50 transition">
                            <span class="font-extrabold text-sm sm:text-base text-gray-900 tracking-wider">POLÍTICA DE REEMBOLSO</span>
                            <span class="w-7 h-7 flex items-center justify-center rounded-full border border-gray-200 text-gray-600 text-lg font-bold transition-transform duration-200" x-text="expanded ? '-' : '+'"></span>
                        </button>
                        <div x-show="expanded" class="px-6 pb-6 text-gray-600 text-sm leading-relaxed border-t border-gray-100 pt-4 whitespace-pre-line">
                            {{ $event->politica_reembolso }}
                        </div>
                    </div>
                    @endif

                </div>

                <!-- DOCUMENTOS -->
                @if(!empty($event->documentos))
                    @php
                        $documentos = is_string($event->documentos) ? json_decode($event->documentos, true) : $event->documentos;
                    @endphp

                    @if(!empty($documentos) && is_array($documentos))
                        <div class="mt-10 pt-8 border-t border-gray-200">
                            <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">DOCUMENTOS</h3>

                            <div class="space-y-4">
                                @foreach($documentos as $doc)
                                    <a href="{{ asset('storage/' . $doc) }}" target="_blank" class="flex items-center p-4 bg-white border border-gray-200 rounded-2xl shadow-sm hover:border-black transition duration-200 group">
                                        <div class="mr-4 shrink-0 text-red-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10">
                                                <path fill-rule="evenodd" d="M5.625 1.5H9.75a1.875 1.875 0 0 1 1.875 1.875v1.5c0 1.036.84 1.875 1.875 1.875h4.5c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H5.625a1.875 1.875 0 0 1-1.875-1.875V3.375C3.75 2.339 4.59 1.5 5.625 1.5ZM12.83 11.75a.75.75 0 0 0-.75-.75h-1.5a.75.75 0 0 0 0 1.5h.75v1.25a.75.75 0 0 0 1.5 0v-2ZM15.75 11a.75.75 0 0 1 .75.75v2a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1-.75-.75v-2a.75.75 0 0 1 .75-.75h1.5Zm-9.75 0a.75.75 0 0 1 .75.75v2a.75.75 0 0 1-.75.75h-.75a.75.75 0 0 1-.75-.75v-2a.75.75 0 0 1 .75-.75h.75Z" clip-rule="evenodd" />
                                                <path d="M12.75 5.25a.75.75 0 0 1 .75-.75h2.25a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75H13.5a.75.75 0 0 1-.75.75v-1.5Z" />
                                            </svg>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h4 class="font-bold text-gray-900 text-sm sm:text-base truncate group-hover:text-black">
                                                {{ basename($doc) }}
                                            </h4>
                                            <p class="text-xs text-gray-500 mt-0.5">Clique aqui para abrir</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

                <!-- MAPA -->
                <div class="mt-10 pt-8 border-t border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">LOCALIZAÇÃO</h3>
                    <div id="map" class="w-full h-80 rounded-3xl shadow-sm border border-gray-200 relative z-0"></div>
                </div>

            </div> <!-- FECHA COLUNA ESQUERDA -->

            <!-- COLUNA DIREITA ORIGINAL -->
            <div class="hidden lg:block lg:col-span-4 mt-10 lg:mt-0">
                <div class="sticky top-10 space-y-6">
                    
                    @php
                        $agora = \Carbon\Carbon::now();
                        $ticketsValidos = $event->tickets->filter(function($t) use ($agora) {
                            if ($t->data_inicio_vendas && $agora->lt($t->data_inicio_vendas)) return false;
                            if ($t->data_fim_vendas && $agora->gt($t->data_fim_vendas)) return false;
                            return true;
                        });
                    @endphp

                    {{-- RUVER: prévia compacta + modal SOMENTE DESKTOP. Checkout GET original preservado. --}}
                    @php
                        $ingressosAgrupadosDesktop = $ticketsValidos->groupBy('modalidade');
                    @endphp
                    <div x-data="{
                        desktopModalOpen: false,
                        tickets: {
                            @foreach($ticketsValidos as $t)
                                '{{ $t->id }}': 0,
                            @endforeach
                        },
                        get totalTickets() {
                            return Object.values(this.tickets).reduce((a, b) => a + Number(b), 0);
                        },
                        openDesktopTickets(groupIndex = null) {
                            this.desktopModalOpen = true;
                            document.body.style.overflow = 'hidden';
                            if (groupIndex !== null) {
                                this.$nextTick(() => {
                                    const heading = document.getElementById('ruver-desktop-group-' + groupIndex);
                                    const scroller = this.$refs.desktopTicketScroll;
                                    if (heading && scroller) {
                                        scroller.scrollTo({ top: heading.offsetTop - scroller.offsetTop - 12, behavior: 'smooth' });
                                    }
                                });
                            }
                        },
                        closeDesktopTickets() {
                            this.desktopModalOpen = false;
                            document.body.style.overflow = '';
                        }
                    }" @keydown.escape.window="if(desktopModalOpen) closeDesktopTickets()">
                        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                            <div class="bg-black p-6 text-center">
                                <h3 class="text-xl font-bold text-white">Ingressos</h3>
                                <p class="text-gray-300 text-sm mt-1">Garanta sua participação neste evento</p>
                            </div>
                            <div class="p-6 space-y-4">
                                @if($event->ja_passou)
                                    <p class="text-sm text-gray-500 text-center">Este evento já foi encerrado.</p>
                                    <a href="{{ url('/evento/' . $event->slug . '/resultados') }}" class="w-full flex items-center justify-center px-4 py-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-2xl transition">Ver Resultado</a>
                                @elseif($ticketsValidos->count() > 0)
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-xs font-extrabold uppercase tracking-wider text-gray-500">Modalidades disponíveis</p>
                                        <span class="text-xs text-gray-500">{{ $ingressosAgrupadosDesktop->count() }} opções</span>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($ingressosAgrupadosDesktop as $modalidade => $ticketsDaModalidade)
                                            @php
                                                $ingressosDisponiveis = $ticketsDaModalidade->filter(fn($t) => $t->quantidade_total == 0 || $t->quantidade_disponivel > 0);
                                                $menorPreco = $ingressosDisponiveis->count() ? $ingressosDisponiveis->min('preco') : null;
                                            @endphp
                                            <button type="button" @click="openDesktopTickets({{ $loop->index }})" class="w-full text-left rounded-xl border border-gray-200 bg-gray-50 p-3 flex items-center justify-between gap-3 hover:border-black hover:bg-white transition">
                                                <span class="min-w-0">
                                                    <span class="block font-bold text-gray-900 text-sm break-words">{{ $modalidade ?: 'Ingressos' }}</span>
                                                    <span class="block text-xs mt-1 {{ $menorPreco === null ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                                        @if($menorPreco === null)
                                                            Esgotado
                                                        @elseif((float) $menorPreco <= 0)
                                                            Gratuito
                                                        @else
                                                            A partir de R$ {{ number_format($menorPreco, 2, ',', '.') }}
                                                        @endif
                                                    </span>
                                                </span>
                                                <span class="text-gray-400 text-xl shrink-0" aria-hidden="true">›</span>
                                            </button>
                                        @endforeach
                                    </div>
                                    <button type="button" @click="openDesktopTickets()" class="w-full py-4 rounded-2xl bg-black text-white font-bold hover:bg-gray-800 transition">COMPRAR INGRESSOS →</button>
                                    <p class="text-xs text-gray-500 text-center">Confira os detalhes antes de continuar</p>
                                @else
                                    <p class="text-sm text-gray-500 text-center">Nenhum ingresso disponível no momento.</p>
                                    <button type="button" disabled class="w-full py-4 rounded-2xl bg-gray-200 text-gray-500 font-bold cursor-not-allowed">INDISPONÍVEL</button>
                                @endif
                            </div>
                        </div>

                        {{-- Modal desktop: nunca interfere no modal mobile já existente. --}}
                        {{-- Teleporta para o body para escapar do stacking context da coluna sticky. --}}
                        <template x-teleport="body">
                        <div x-show="desktopModalOpen" x-cloak class="ruver-desktop-modal fixed inset-0 flex items-center justify-center p-4" style="display: none; z-index: 2147483647;" role="dialog" aria-modal="true" aria-labelledby="ruverDesktopTicketsTitle">
                            <div class="absolute inset-0 bg-black/70" @click="closeDesktopTickets()"></div>
                            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden" @click.stop>
                                <div class="flex items-start justify-between gap-4 p-6 border-b border-gray-200 bg-white shrink-0">
                                    <div>
                                        <h2 id="ruverDesktopTicketsTitle" class="text-2xl font-extrabold text-gray-900">Escolha seus ingressos</h2>
                                        <p class="text-sm text-gray-500 mt-1">{{ $event->titulo }}</p>
                                    </div>
                                    <button type="button" @click="closeDesktopTickets()" class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-3xl leading-none shrink-0" aria-label="Fechar ingressos">×</button>
                                </div>
                                <div x-ref="desktopTicketScroll" class="overflow-y-auto overscroll-contain p-6 relative">
                                    <form action="{{ route('checkout') }}" method="GET">
                                        @forelse($ingressosAgrupadosDesktop as $modalidade => $tickets)
                                            <section class="mb-6">
                                                <h4 id="ruver-desktop-group-{{ $loop->index }}" class="text-xl font-extrabold text-gray-900 px-5 pt-5 pb-3 bg-gray-100 border border-gray-200 border-b-0 rounded-t-2xl">{{ $modalidade ?: 'Ingressos' }}</h4>
                                                <div class="space-y-3 bg-gray-100 border border-gray-200 border-t-0 rounded-b-2xl p-4">
                                                    @foreach($tickets as $ticket)
                                                        @php
                                                            $isIlimitado = $ticket->quantidade_total == 0;
                                                            $isEsgotado = !$isIlimitado && $ticket->quantidade_disponivel <= 0;
                                                        @endphp
                                                        <div class="flex flex-col sm:flex-row border border-gray-200 rounded-2xl overflow-hidden {{ $isEsgotado ? 'opacity-60 bg-gray-50' : 'hover:border-black transition duration-200 group bg-white' }}">
                                                            <div class="p-5 flex-1 relative">
                                                                <h5 class="font-bold text-lg text-gray-900 mb-1">{{ $ticket->nome }}</h5>
                                                                <p class="text-sm text-red-600 mb-4 whitespace-pre-line break-words [overflow-wrap:anywhere]">{{ $ticket->descricao }}</p>
                                                                <div class="mt-auto">
                                                                    <span class="text-2xl font-extrabold text-gray-900">R$ {{ number_format($ticket->preco, 2, ',', '.') }}</span>
                                                                    <div class="text-xs mt-1 {{ $isEsgotado ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                                                        @if($isEsgotado) ESGOTADO @elseif($isIlimitado) Disponibilidade Ilimitada @else Restam {{ $ticket->quantidade_disponivel }} @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="hidden sm:block border-l-2 border-dashed border-gray-200 relative"></div>
                                                            <div class="sm:hidden border-t-2 border-dashed border-gray-200"></div>
                                                            <div class="p-5 flex items-center justify-center sm:w-40 shrink-0">
                                                                @if($isEsgotado)
                                                                    <span class="font-bold text-red-500 bg-red-50 px-4 py-2 rounded-xl text-sm border border-red-100">Esgotado</span>
                                                                @else
                                                                    <div class="flex items-center space-x-4">
                                                                        <button type="button" @click="if(tickets['{{ $ticket->id }}'] > 0) tickets['{{ $ticket->id }}']--" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 hover:bg-gray-100 text-gray-600 transition focus:outline-none border border-gray-100" aria-label="Diminuir quantidade">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                                                                        </button>
                                                                        <span class="font-bold text-gray-900 text-lg w-4 text-center" x-text="tickets['{{ $ticket->id }}']">0</span>
                                                                        <button type="button" @click="if({{ $isIlimitado ? 'true' : "tickets['{$ticket->id}'] < {$ticket->quantidade_disponivel}" }}) tickets['{{ $ticket->id }}']++" class="w-8 h-8 flex items-center justify-center rounded-full bg-black hover:bg-gray-800 text-white transition focus:outline-none border border-black" aria-label="Aumentar quantidade">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                                                        </button>
                                                                    </div>
                                                                    <input type="hidden" name="tickets[{{ $ticket->id }}]" :value="tickets['{{ $ticket->id }}']">
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </section>
                                        @empty
                                            <p class="text-center text-gray-500 py-6">Nenhum ingresso disponível.</p>
                                        @endforelse
                                        @if($ticketsValidos->count() > 0 && !$event->ja_passou)
                                            <div class="sticky bottom-0 bg-white border-t border-gray-100 pt-4 pb-2 mt-6">
                                                <button type="submit" :disabled="totalTickets === 0" :class="totalTickets > 0 ? 'bg-black hover:bg-gray-800 text-white shadow-md' : 'bg-gray-100 text-gray-400 cursor-not-allowed'" class="w-full flex items-center justify-center px-4 py-4 text-base font-bold rounded-2xl transition-all duration-300">
                                                    <span x-text="totalTickets > 0 ? 'Comprar (' + totalTickets + ')' : 'Selecione os ingressos'"></span>
                                                    <svg x-show="totalTickets > 0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 ml-2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                                                </button>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>
                        </template>
                    </div>

                    <!-- CARD EXTRAS -->
                    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 space-y-6">
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3 text-sm">Precisa de ajuda?</h4>
                            <ul class="space-y-3 text-sm">
                                @if($event->organizer && $event->organizer->whatsapp)
                                    <li>
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $event->organizer->whatsapp) }}" target="_blank" class="flex items-center text-gray-600 hover:text-black transition font-medium">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-2 text-gray-400"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-4.03 9-9s-4.03-9-9-9-9 4.03-9 9 4.03 9 9 9z" /><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 13.5l1.5-1.5m0 0l1.5-1.5m-1.5 1.5v-3.75" /></svg>
                                            Fale com o organizador
                                        </a>
                                    </li>
                                @else
                                    <li><span class="text-gray-400 italic text-xs">Suporte via WhatsApp indisponível.</span></li>
                                @endif
                            </ul>
                        </div>
                        @if($event->organizer && !empty($event->organizer->bio))
                            <div class="border-t border-gray-100 pt-5">
                                <h4 class="font-bold text-gray-900 mb-3 text-sm">Sobre o Organizador</h4>
                                <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $event->organizer->bio }}</p>
                            </div>
                        @endif
                    </div>
            </div> 
        </div> 
    </main>

    <!-- BARRA FIXA FLUTUANTE (APENAS CELULAR) -->
    <div class="fixed bottom-0 left-0 right-0 p-4 bg-white/90 backdrop-blur-md border-t border-gray-200 shadow-[0_-10px_20px_-5px_rgba(0,0,0,0.1)] z-40 lg:hidden">
        @if($event->ja_passou)
            <a href="{{ url('/evento/' . $event->slug . '/resultados') }}" class="w-full flex items-center justify-center px-4 py-4 bg-red-600 hover:bg-red-700 text-white text-base font-bold rounded-2xl shadow-lg transition-all">
                Ver Resultado
              
            </a>
        @else
            <button type="button" @click="showMobileModal = true" class="w-full flex items-center justify-center px-4 py-4 bg-black text-white text-base font-bold rounded-2xl shadow-lg hover:bg-gray-800 transition-all">
                Comprar Ingressos
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 ml-2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
            </button>
        @endif
    </div>

    <!-- MODAL DE INGRESSOS COMPLETO (APENAS CELULAR) -->
    <div x-show="showMobileModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-full"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-full"
         class="fixed inset-0 z-50 bg-white flex flex-col lg:hidden"
         style="display: none;">
        
        <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-white sticky top-0 z-10 shadow-sm">
            <h3 class="text-xl font-extrabold text-gray-900">Selecionar Ingressos</h3>
            <button type="button" @click="showMobileModal = false" class="p-2 bg-gray-100 rounded-full hover:bg-gray-200 text-gray-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="p-6 overflow-y-auto flex-1">
            <form action="{{ route('checkout') }}" method="GET">
                @php
                    $ingressosAgrupados = $ticketsValidos->groupBy('modalidade');
                @endphp

                <div x-data="{
                    ticketsMobile: {
                        @foreach($ticketsValidos as $t)
                            '{{ $t->id }}': 0,
                        @endforeach
                    },
                    get totalTicketsMobile() {
                        return Object.values(this.ticketsMobile).reduce((a, b) => a + b, 0);
                    }
                }">
                    @forelse($ingressosAgrupados as $modalidade => $tickets)
                        <h4 class="text-xl font-extrabold text-gray-900 mb-4 mt-4 first:mt-0">{{ $modalidade ?: 'Ingressos' }}</h4>
                        <div class="space-y-4 mb-6">
                            @foreach($tickets as $ticket)
                                @php
                                    $isIlimitado = $ticket->quantidade_total == 0;
                                    $isEsgotado = !$isIlimitado && $ticket->quantidade_disponivel <= 0;
                                @endphp
                                <div class="border border-gray-200 rounded-2xl p-4 {{ $isEsgotado ? 'opacity-60 bg-gray-50' : 'bg-white' }}">
                                    <h5 class="font-bold text-base text-gray-900 mb-1">{{ $ticket->nome }}</h5>
                                    <p class="text-xs text-red-600 mb-3">{{ $ticket->descricao }}</p>
                                    
                                    <div class="flex items-center justify-between mt-2">
                                        <div>
                                            <span class="text-xl font-extrabold text-gray-900">R$ {{ number_format($ticket->preco, 2, ',', '.') }}</span>
                                            <div class="text-[10px] {{ $isEsgotado ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                                @if($isEsgotado) ESGOTADO @elseif($isIlimitado) Ilimitado @else Restam {{ $ticket->quantidade_disponivel }} @endif
                                            </div>
                                        </div>

                                        <div>
                                            @if($isEsgotado)
                                                <span class="font-bold text-red-500 bg-red-50 px-3 py-1 rounded-lg text-xs border border-red-100">Esgotado</span>
                                            @else
                                                <div class="flex items-center space-x-3">
                                                    <button type="button" @click="if(ticketsMobile['{{ $ticket->id }}'] > 0) ticketsMobile['{{ $ticket->id }}']--" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-700 font-bold border">-</button>
                                                    <span class="font-bold text-gray-900 text-base" x-text="ticketsMobile['{{ $ticket->id }}']">0</span>
                                                    <button type="button" @click="if({{ $isIlimitado ? 'true' : "ticketsMobile['{$ticket->id}'] < {$ticket->quantidade_disponivel}" }}) ticketsMobile['{{ $ticket->id }}']++" class="w-8 h-8 flex items-center justify-center rounded-full bg-sky-100 text-sky-700 font-bold border border-sky-200">+</button>
                                                </div>
                                                <input type="hidden" name="tickets[{{ $ticket->id }}]" :value="ticketsMobile['{{ $ticket->id }}']">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-6">Nenhum ingresso disponível.</p>
                    @endforelse

                    @if($ticketsValidos->count() > 0)
                        <div class="sticky bottom-0 bg-white pt-4 pb-6 border-t border-gray-100 mt-6">
                            <button type="submit" :disabled="totalTicketsMobile === 0" :class="totalTicketsMobile > 0 ? 'bg-black hover:bg-gray-800 text-white shadow-md' : 'bg-gray-100 text-gray-400 cursor-not-allowed'" class="w-full py-4 text-base font-bold rounded-2xl transition-all">
                                <span x-text="totalTicketsMobile > 0 ? 'Avançar (' + totalTicketsMobile + ')' : 'Selecione os ingressos'"></span>
                            </button>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-black text-gray-400 pt-6 pb-24 lg:pb-6 mt-12 border-t border-gray-900 text-xs mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-gray-300">&copy; {{ date('Y') }} {{ config('app.name', 'IngressosApp') }}. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        function countdown(expiryDate) {
            return {
                days: '00', hours: '00', minutes: '00', seconds: '00',
                start() {
                    const countDownDate = new Date(expiryDate).getTime();
                    setInterval(() => {
                        const now = new Date().getTime();
                        const distance = countDownDate - now;
                        if (distance < 0) {
                            this.days = '00'; this.hours = '00'; this.minutes = '00'; this.seconds = '00';
                            return;
                        }
                        this.days = String(Math.floor(distance / (1000 * 60 * 60 * 24))).padStart(2, '0');
                        this.hours = String(Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
                        this.minutes = String(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
                        this.seconds = String(Math.floor((distance % (1000 * 60)) / 1000)).padStart(2, '0');
                    }, 1000);
                }
            }
        }
    </script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fullAddress = "{{ $event->endereco }}, {{ $event->cidade }}, {{ $event->estado }}, Brasil";
            const fallbackAddress = "{{ $event->cidade }}, {{ $event->estado }}, Brasil";
            
            const map = L.map('map').setView([-14.2350, -51.9253], 4);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            function setLocation(lat, lon, popupText, zoom) {
                map.setView([lat, lon], zoom);
                L.marker([lat, lon]).addTo(map).bindPopup(popupText).openPopup();
            }

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(fullAddress)}&limit=1`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        setLocation(data[0].lat, data[0].lon, `<b>{{ $event->titulo }}</b><br>{{ $event->endereco }}`, 16);
                    } else {
                        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(fallbackAddress)}&limit=1`)
                            .then(res => res.json())
                            .then(cityData => {
                                if (cityData && cityData.length > 0) {
                                    setLocation(cityData[0].lat, cityData[0].lon, `<b>{{ $event->cidade }} - {{ $event->estado }}</b>`, 13);
                                }
                            });
                    }
                }).catch(error => console.log('Erro ao buscar localização:', error));
        });
    </script>

</body>
</html>