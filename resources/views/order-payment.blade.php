<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    @php
        // Resgata o organizador exclusivamente pelo domínio (Middleware), garantindo que no site principal seja Ruver
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

    <title>Pagamento do Pedido #{{ $order->id }} - {{ config('app.name', 'Ruver') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased flex flex-col min-h-screen">

    <!-- HEADER / NAV Dinâmico -->
    <nav class="shadow-md" style="background-color: {{ $brandColor }};">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <a href="{{ route('home') }}" class="flex items-center">
                <img src="{{ $brandLogo }}" alt="Logo" class="h-6 w-auto object-contain">
            </a>
            <a href="{{ route('my.tickets') }}" class="text-xs font-bold text-gray-300 hover:text-white transition">
                &larr; Ir para Meus Ingressos
            </a>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full space-y-8">

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-xs font-bold shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="text-center space-y-2">

            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Status da Inscrição</h1>
            <p class="text-xs text-gray-500">Pedido #{{ $order->id }} — {{ $order->ticket->event->titulo }}</p>
        </div>

        <div class="bg-white border border-gray-100 shadow-xl rounded-3xl p-6 sm:p-8 space-y-8">

            <!-- RESUMO DO PEDIDO -->
            <div class="bg-gray-50/80 rounded-2xl p-5 border border-gray-200 space-y-3">
                <div class="flex justify-between items-center border-b border-gray-200 pb-3">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Evento</span>
                    <span class="text-sm font-extrabold text-gray-900">{{ $order->ticket->event->titulo }}</span>
                </div>
                <div class="flex justify-between items-center border-b border-gray-200 pb-3">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Ingresso</span>
                    <span class="text-xs font-bold text-sky-600">{{ $order->quantidade }}x {{ $order->ticket->nome }}</span>
                </div>
                <div class="flex justify-between items-center pt-1">
                    <span class="text-sm font-bold text-gray-900">Total a Pagar</span>
                    <span class="text-2xl font-extrabold text-emerald-600 font-mono">R$ {{ number_format($order->valor_total, 2, ',', '.') }}</span>
                </div>
            </div>

            <!-- OPÇÃO 1: PIX -->
            @if($formaPagamento === 'PIX' || !$formaPagamento)
                <div class="space-y-6 text-center">
                    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-emerald-800 text-xs font-semibold">
                        Escaneie o QR Code ou copie o código Pix abaixo para realizar o pagamento instantâneo.
                    </div>

                    <!-- AVISO DE TEMPO DO PIX -->
                    <div class="text-center mb-4">
                        <p class="text-xs font-bold text-gray-600">Este QR Code expira em:</p>
                        <div class="text-3xl font-mono font-extrabold text-red-600 mt-1" id="pix-timer">
                            05:00
                        </div>
                    </div>

                    <!-- QR CODE VISUAL -->
                    <div class="flex flex-col items-center justify-center p-4 bg-white border border-gray-200 rounded-2xl shadow-sm w-48 h-48 mx-auto relative">
                        <div id="qrcode-pix"></div>
                    </div>

                    <!-- CÓDIGO COPIA E COLA -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide">Código Pix Copia e Cola</label>
                        <div class="flex items-center space-x-2 max-w-md mx-auto">
                            <input type="text" id="pixCopiaCola" readonly value="{{ $pixCopiaCola }}" class="w-full bg-gray-50 border-gray-300 rounded-xl text-xs font-mono p-3 text-gray-700 border select-all">
                            <button type="button" onclick="copiarPix()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-3 rounded-xl transition shrink-0 shadow-md flex items-center">
                                📋 Copiar
                            </button>
                        </div>
                        <p id="msgCopiado" class="text-xs text-emerald-600 font-bold hidden mt-1">✓ Código Pix copiado para a área de transferência!</p>
                    </div>

                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            new QRCode(document.getElementById("qrcode-pix"), {
                                text: "{{ $pixCopiaCola }}",
                                width: 160,
                                height: 160,
                                colorDark : "#000000",
                                colorLight : "#ffffff",
                                correctLevel : QRCode.CorrectLevel.M
                            });
                        });

                        function copiarPix() {
                            const input = document.getElementById('pixCopiaCola');
                            input.select();
                            navigator.clipboard.writeText(input.value);
                            const msg = document.getElementById('msgCopiado');
                            msg.classList.remove('hidden');
                            setTimeout(() => msg.classList.add('hidden'), 3000);
                        }
                    </script>
                </div>
            @endif

            <!-- OPÇÃO 2: BOLETO -->
            @if($formaPagamento === 'BOLETO')
                <div class="space-y-6 text-center">
                    <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4 text-indigo-800 text-xs font-semibold">
                        Seu Boleto Bancário foi gerado com sucesso. Clique no botão abaixo para visualizar e pagar.
                    </div>

                    <div class="p-6 bg-gray-50 border border-gray-200 rounded-2xl space-y-4 max-w-md mx-auto">
                        <span class="text-3xl">📄</span>
                        <p class="text-xs text-gray-600">O pagamento via boleto pode levar até 2 dias úteis para ser compensado na plataforma.</p>
                        
                        @if($bankSlipUrl)
                            <a href="{{ $bankSlipUrl }}" target="_blank" class="block w-full bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-1 text-white font-bold text-xs py-3 rounded-xl transition shadow-md text-center" style="-webkit-tap-highlight-color: transparent;">
                                Abrir Boleto Bancário (PDF)
                            </a>
                        @else
                            <button type="button" onclick="alert('Link do boleto indisponível no momento. Verifique seus e-mails ou entre em contato com o suporte.')" class="w-full bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-1 text-white font-bold text-xs py-3 rounded-xl transition shadow-md" style="-webkit-tap-highlight-color: transparent;">
                                Ver Boleto
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            <!-- OPÇÃO 3: CARTÃO DE CRÉDITO -->
            @if($formaPagamento === 'CREDIT_CARD')
                <div class="space-y-6 text-center">
                    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-emerald-800 text-xs font-semibold">
                        💳 Transação via Cartão de Crédito processada!
                    </div>
                    
                    <div class="p-6 bg-gray-50 border border-gray-200 rounded-2xl space-y-4 max-w-md mx-auto">
                        <span class="text-3xl">✅</span>
                        <p class="text-xs text-gray-600 font-bold">Seu pagamento foi enviado para aprovação junto à operadora.</p>
                        <p class="text-xs text-gray-500">Você receberá um e-mail de confirmação assim que o pagamento for aprovado pelo banco. Seus ingressos ficarão disponíveis na aba "Meus Ingressos".</p>
                    </div>
                </div>
            @endif

            <!-- BOTÃO DE VOLTAR APENAS -->
            <div class="pt-6 border-t border-gray-100 flex justify-center items-center">
                <a href="{{ route('my.tickets') }}" class="text-xs font-bold text-gray-500 hover:text-gray-800 transition">
                    Ver Meus Ingressos
                </a>
            </div>

        </div>

    </main>

    <!-- Script da Mágica (Polling + Cronômetro Real) - AGORA SÓ RODA SE FOR PIX! -->
    @if($order->status === 'pendente' && ($formaPagamento === 'PIX' || !$formaPagamento))
    <script>
        // Pega a data de expiração REAL gerada pelo servidor (Criado em + 5 minutos)
        const expirationTime = new Date("{{ $order->created_at->addMinutes(5)->toIso8601String() }}").getTime();
        const timerDisplay = document.getElementById('pix-timer');

        // 1. Cronômetro Visual (Roda a cada 1 segundo)
        const clockInterval = setInterval(() => {
            const now = new Date().getTime();
            const distance = expirationTime - now;

            // Calcula minutos e segundos
            let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Quando o tempo acabar...
            if (distance <= 0) {
                clearInterval(clockInterval);
                if (typeof pollingInterval !== 'undefined') clearInterval(pollingInterval);
                
                if(timerDisplay) timerDisplay.textContent = "00:00";
                
                alert("O tempo para pagamento expirou! O pedido será cancelado.");
                window.location.href = "{{ route('home') }}"; 
                return;
            }

            if(timerDisplay) {
                timerDisplay.textContent = 
                    String(minutes).padStart(2, '0') + ':' + 
                    String(seconds).padStart(2, '0');
            }
        }, 1000);

        // 2. Verificador de Pagamento (Roda a cada 5 segundos)
        const pollingInterval = setInterval(() => {
            fetch("{{ route('order.check_status', $order->id) }}", {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'pago') {
                    clearInterval(clockInterval);
                    clearInterval(pollingInterval);
                    window.location.href = "{{ route('my.tickets') }}"; 
                } 
                else if (data.status === 'cancelado' || data.status === 'expirado') {
                    clearInterval(clockInterval);
                    clearInterval(pollingInterval);
                    alert("Este pedido foi cancelado/expirado.");
                    window.location.href = "{{ route('home') }}"; 
                }
            })
            .catch(error => console.error('Erro ao verificar status:', error));
        }, 5000);
    </script>
    @endif

</body>
</html>