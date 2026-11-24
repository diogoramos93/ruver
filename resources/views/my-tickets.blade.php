<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        @php
            // Resgata o organizador injetado pelo Middleware na request ou tenta pegar pelo primeiro ingresso do usuário
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

        <title>Meus Ingressos - {{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <!-- Adicionado a biblioteca do QR Code -->
        <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    </head>
    <body class="bg-gray-50 text-gray-900 font-sans antialiased flex flex-col min-h-screen">

<!-- Navbar Dinâmica -->
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
        <main class="max-w-3xl mx-auto px-4 py-8 flex-grow w-full">
            
            <div class="mb-6">
                <h1 class="text-xl font-bold text-gray-900">Meus Ingressos</h1>
                <p class="text-xs text-gray-500 mt-0.5">Acompanhe seus pedidos e acesse as credenciais.</p>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="space-y-4">
                @forelse($orders as $order)
                    <!-- Card Compacto do Ingresso -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
                        
                        <div class="p-4 sm:p-5">
                            <!-- Cabeçalho do Card (com o Título e Status) -->
                            <div class="flex items-start justify-between">
                                <div class="pr-4 flex-1">
                                    <div class="flex items-center space-x-2 mb-1.5">
                                        <!-- LOGICA DE STATUS DINÂMICO -->
                                        @if($order->status === 'pago')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 uppercase tracking-wide">
                                                Pago
                                            </span>
                                        @elseif($order->status === 'pendente')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-yellow-100 text-yellow-800 uppercase tracking-wide">
                                                Aguardando Pagamento
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 uppercase tracking-wide">
                                                Cancelado
                                            </span>
                                        @endif

                                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Pedido #{{ $order->id }}</span>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 leading-tight mb-2">
                                        {{ $order->ticket->event->titulo }}
                                    </h3>
                                    
                                    <div class="text-[11px] text-gray-600 space-y-1">
                                        <p><span class="font-bold text-gray-800">🎟️ Tipo:</span> {{ $order->ticket->nome }} | Qtd: {{ $order->quantidade }}</p>
                                        <p><span class="font-bold text-gray-800">📅 Data:</span> {{ \Carbon\Carbon::parse($order->ticket->event->data_inicio)->format('d/m/Y H:i') }}</p>
                                        <p><span class="font-bold text-gray-800">📍 Local:</span> {{ $order->ticket->event->cidade }}, {{ $order->ticket->event->estado }}</p>
                                    </div>
                                </div>
                                
                                <!-- AÇÕES (QR CODE E BOTÃO OU BOTÃO DE PAGAR) -->
                                @if($order->status === 'pago')
                                    @php
                                        $meuIngresso = $order->participants->firstWhere('cpf', auth()->user()->cpf);
                                        if(!$meuIngresso && $order->participants->count() > 0) {
                                            $meuIngresso = $order->participants->first();
                                        }
                                    @endphp
                                    
                                    @if($meuIngresso)
                                        @php $linkMeuIngresso = URL::signedRoute('ticket.show', $meuIngresso->id); @endphp
                                        <div class="flex flex-col items-center shrink-0">
                                            <!-- QR Code Clicável -->
                                            <a href="{{ $linkMeuIngresso }}" target="_blank" class="block bg-white p-1 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition" title="Clique para ampliar">
                                                <div id="qrcode-{{ $meuIngresso->id }}"></div>
                                            </a>
                                            
                                            <!-- 👇 NOVO BOTÃO ÓBVIO PARA VER O INGRESSO 👇 -->
                                            <a href="{{ $linkMeuIngresso }}" target="_blank" class="mt-2 bg-black text-white text-[10px] font-bold px-3 py-1.5 rounded-md hover:bg-gray-800 transition shadow-sm flex items-center justify-center w-full">
                                                🎟️ Ver Ingresso
                                            </a>
                                            
                                            <script type="text/javascript">
                                                document.addEventListener("DOMContentLoaded", function() {
                                                    new QRCode(document.getElementById("qrcode-{{ $meuIngresso->id }}"), {
                                                        text: "{{ $linkMeuIngresso }}",
                                                        width: 65,
                                                        height: 65,
                                                        colorDark : "#000000",
                                                        colorLight : "#ffffff",
                                                        correctLevel : QRCode.CorrectLevel.L
                                                    });
                                                });
                                            </script>
                                        </div>
                                    @endif
                                @elseif($order->status === 'pendente')
                                    <div class="flex flex-col items-center shrink-0">
                                        <a href="{{ route('order.payment', $order->id) }}" class="bg-black text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-gray-800 transition text-center shadow-md">
                                            Pagar<br>Agora
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <!-- SEÇÃO DOS PARTICIPANTES -->
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-wide">Participantes do Pedido:</p>
                                <div class="space-y-1.5">
                                    @foreach($order->participants as $index => $participant)
                                        <div class="bg-gray-50/80 p-2 rounded-lg border border-gray-100 text-[11px] flex justify-between items-center">
                                            <div class="truncate pr-2">
                                                <span class="font-bold text-gray-900">{{ $index + 1 }}. {{ $participant->nome }}</span>
                                                <span class="text-gray-500 hidden sm:inline"> | {{ $participant->sexo }}, Nasc: {{ $participant->data_nascimento->format('d/m/Y') }}{{ $participant->equipe ? ', Equipe: ' . $participant->equipe : '' }}</span>
                                            </div>
                                            
                                            <!-- BOTÃO WHATSAPP SÓ APARECE SE TIVER PAGO -->
                                            @if($order->status === 'pago')
                                                @php
                                                    $linkIngresso = URL::signedRoute('ticket.show', $participant->id);
                                                    $mensagemZap = urlencode("Olá, {$participant->nome}! Aqui está o seu ingresso para o evento {$order->ticket->event->titulo}. Acesse o link para ver seu QR Code: {$linkIngresso}");
                                                @endphp
                                                
                                                <a href="https://api.whatsapp.com/send?text={{ $mensagemZap }}" target="_blank" class="text-green-600 hover:text-green-700 font-bold flex items-center shrink-0 bg-green-50 px-2 py-1 rounded border border-green-100">
                                                    📲 Enviar Zap
                                                </a>
                                            @else
                                                <span class="text-gray-400 font-bold flex items-center shrink-0 bg-gray-100 px-2 py-1 rounded">
                                                    ⏳ Aguardando
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>

                        <!-- Rodapé do Card -->
                        <div class="bg-gray-50 px-4 sm:px-5 py-2.5 border-t border-gray-100 flex items-center justify-between">
                            <div class="text-[11px] text-gray-500">
                                Total: <span class="font-bold text-gray-900">R$ {{ number_format($order->valor_total, 2, ',', '.') }}</span>
                            </div>
                            <a href="{{ route('event.show', $order->ticket->event->slug ?? $order->ticket->event->id) }}" class="text-blue-600 hover:text-blue-800 text-[11px] font-bold transition">
                                Ver página do evento &rarr;
                            </a>
                        </div>
                    </div>

                @empty
                    <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                        <div class="text-3xl mb-2">🎟️</div>
                        <h3 class="text-sm font-bold text-gray-900">Nenhum pedido encontrado</h3>
                        <a href="{{ route('home') }}" class="inline-block mt-4 bg-black text-white text-[11px] font-bold px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                            Explorar Eventos
                        </a>
                    </div>
                @endforelse
            </div>

            @if($orders->hasPages())
                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            @endif

        </main>

        <!-- MENU FLUTUANTE DE 5 ÍCONES (APENAS CELULAR) -->
        <div class="fixed bottom-4 left-4 right-4 z-50 lg:hidden">
            <div class="bg-[#2a2a2c] rounded-[2rem] px-6 py-1.5 flex justify-between items-center shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5">
                
                <!-- Ícone 1: Início (Normal) -->
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

                <!-- Ícone 3: Troféu -->
                <a href="{{ route('resultados.index') }}" class="w-12 h-12 flex items-center justify-center text-white/80 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                    </svg>

                </a>

                <!-- Ícone 4: Ingresso -->
                <a href="{{ route('my.tickets') }}" class="w-12 h-12 flex items-center justify-center bg-[#39ff14] rounded-full text-black hover:scale-105 transition-transform shadow-[0_0_15px_rgba(57,255,20,0.3)]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
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

        <footer class="bg-black text-gray-400 py-6 mt-auto border-t border-gray-900 text-xs pb-28 lg:pb-6">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <p>&copy; {{ date('Y') }} Ruver. Todos os direitos reservados.</p>
            </div>
        </footer>

    </body>
</html>