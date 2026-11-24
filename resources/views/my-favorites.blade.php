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

        <title>Meus Favoritos - {{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 text-gray-900 font-sans antialiased flex flex-col min-h-screen">

        <!-- Navbar Dinâmica -->
        <nav class="shadow-md relative z-40" style="background-color: {{ $brandColor }};">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
                <a href="{{ route('home') }}" class="flex items-center">
                    <img src="{{ $brandLogo }}" alt="Logo" class="h-6 w-auto object-contain">
                </a>
                <div class="hidden lg:flex items-center space-x-4">
                    <a href="{{ route('my.tickets') }}" class="text-sm font-semibold text-gray-300 hover:text-white transition">Meus Ingressos</a>
                    <a href="{{ route('cliente.dashboard') }}" class="text-sm font-semibold text-gray-300 hover:text-white transition">← Voltar ao Painel</a>
                </div>
            </div>
        </nav>

        <!-- Conteúdo Principal -->
        <main class="max-w-5xl mx-auto px-4 py-10 flex-grow w-full">
            
            <div class="mb-10 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Meus Favoritos ❤️</h1>
                    <p class="text-sm text-gray-500 mt-1">Eventos que você salvou para não perder de vista.</p>
                </div>
            </div>

            <!-- Exibição das Mensagens de Sucesso -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Grid de Eventos Favoritados -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                
                @forelse($favoritos as $event)
                    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl flex flex-col group relative">
                        
                        <!-- Imagem do Evento (Usando o padrão exato da Home) -->
                        <a href="{{ route('event.show', $event->slug ?? $event->id) }}" class="w-full aspect-square bg-gray-200 overflow-hidden relative block">
                            @if($event->imagem_card)
                                <img src="{{ asset('storage/' . $event->imagem_card) }}" alt="{{ $event->titulo }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">Sem imagem</div>
                            @endif
                        </a>

                        <!-- Conteúdo do Card -->
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            
                            <!-- Bloco com Caixinha de Data na Esquerda e Endereço/Título na Direita -->
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

                            <!-- Botões de Ação (Ver Evento e Remover Favorito) -->
                            <div class="mt-4 flex gap-2 justify-end">
                                <a href="{{ route('event.show', $event->slug ?? $event->id) }}" class="bg-black text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-gray-800 transition flex-grow text-center">
                                    Ver evento
                                </a>
                                
                                <!-- Botão de Remover -->
                                <form action="{{ route('favoritos.toggle', $event->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-red-50 text-red-500 px-3 py-2 rounded-lg hover:bg-red-100 transition" title="Remover dos favoritos">
                                        ❌
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white p-10 rounded-2xl border border-gray-200 text-center">
                        <div class="text-4xl mb-4">💔</div>
                        <h3 class="text-lg font-bold text-gray-900">Você ainda não tem favoritos</h3>
                        <p class="text-gray-500 mt-2">Navegue pelos eventos e clique no coração para salvar os que você mais gostar.</p>
                        <a href="{{ route('home') }}" class="inline-block mt-6 bg-black text-white px-6 py-3 rounded-lg text-sm font-semibold hover:bg-gray-800 transition">
                            Explorar Eventos
                        </a>
                    </div>
                @endforelse

            </div>

            <!-- Paginação -->
            <div class="mt-8">
                {{ $favoritos->links() }}
            </div>
            
        </main>
    </body>
</html>