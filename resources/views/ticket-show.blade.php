<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    @php
        $eventOrganizer = $participant->ticket->event->organizer ?? null;

        $brandLogo = ($eventOrganizer && $eventOrganizer->logo) 
            ? asset('storage/' . $eventOrganizer->logo) 
            : asset('images/1-Ruver Logo.svg');

        $brandColor = ($eventOrganizer && $eventOrganizer->cor_marca) 
            ? $eventOrganizer->cor_marca 
            : '#000000';

        $brandIcon = ($eventOrganizer && $eventOrganizer->icone) 
            ? asset('storage/' . $eventOrganizer->icone) 
            : asset('images/favicon.png');
            
        $isMeiaIdoso = false;
        if($participant->ticket->aceita_desconto_idoso && !empty($participant->data_nascimento)) {
            $idade = \Carbon\Carbon::parse($participant->data_nascimento)->age;
            if($idade >= 60) {
                $isMeiaIdoso = true;
            }
        }
        
        $order = $participant->order; 
    @endphp

    <link rel="icon" type="image/png" href="{{ $brandIcon }}">

    <title>Ingresso - {{ $participant->ticket->event->titulo }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
</head>
<!-- A MÁGICA ESTÁ AQUI NO BODY: print:block print:min-h-0 print:py-0 -->
<body class="bg-gray-100 text-gray-900 font-sans antialiased flex flex-col items-center justify-center min-h-screen py-8 px-4 print:block print:min-h-0 print:py-0 print:px-0">

    <div class="ticket-container bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-gray-100 mx-auto">
        
        <!-- HEADER DO INGRESSO -->
        <div class="text-white p-6 print:p-4 text-center relative flex flex-col items-center justify-center" style="background-color: {{ $brandColor }};">
            <div class="mb-3 print:mb-2">
                <img src="{{ $brandLogo }}" alt="Logo" class="h-10 w-auto object-contain">
            </div>
            
            <p class="text-xs font-bold uppercase tracking-widest text-white/80 mb-1">
                {{ $eventOrganizer->nome ?? 'Plataforma' }}
            </p>
            <h1 class="text-xs font-extrabold uppercase tracking-widest mt-2 bg-white/20 px-3 py-1 rounded">
                COMPROVANTE DE INSCRIÇÃO
            </h1>
        </div>

        <div class="p-6 print:p-4 space-y-6 print:space-y-3">
            
            <!-- DADOS DO ATLETA -->
            <div>
                <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-1 mb-2">Atleta</h3>
                <h2 class="text-xl font-extrabold text-gray-900 uppercase">{{ $participant->nome }}</h2>
                <div class="text-sm text-gray-600 mt-1 space-y-0.5">
                    <p>CPF/Doc: <strong>{{ $participant->cpf ?: $participant->passaporte }}</strong></p>
                    <p>Data de Nascimento: <strong>{{ $participant->data_nascimento ? \Carbon\Carbon::parse($participant->data_nascimento)->format('d/m/Y') : 'Não informada' }}</strong></p>
                    @if($participant->equipe)
                        <p>Equipe: <strong>{{ $participant->equipe }}</strong></p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 print:gap-2">
                <!-- SITUAÇÃO -->
                <div>
                    <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-1 mb-2">Situação</h3>
                    <span class="inline-block bg-green-100 text-green-700 border border-green-300 text-xs font-extrabold px-2 py-1 rounded uppercase tracking-wide">
                        Confirmado
                    </span>
                </div>
                <!-- VALIDADOR (ID) -->
                <div>
                    <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-1 mb-2">Validador</h3>
                    <span class="text-sm font-extrabold text-gray-900 tracking-wider">
                        RVR-{{ str_pad($participant->id, 6, '0', STR_PAD_LEFT) }}
                    </span>
                </div>
            </div>

            <!-- DADOS DO EVENTO -->
            <div>
                <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-1 mb-2">Evento</h3>
                <h4 class="text-lg font-bold text-gray-900 uppercase">{{ $participant->ticket->event->titulo }}</h4>
                <div class="text-sm text-gray-600 mt-1 grid grid-cols-2 gap-y-2 mt-2">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold">Data</p>
                        <p class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($participant->ticket->event->data_inicio)->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold">Local</p>
                        <p class="font-bold text-gray-800">{{ $participant->ticket->event->cidade }} - {{ $participant->ticket->event->estado }}</p>
                    </div>
                </div>
            </div>

            <!-- CATEGORIA E PRODUTOS -->
            <div class="bg-gray-50 print:bg-gray-50/50 rounded-xl p-4 print:p-3 border border-gray-100">
                <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-200 pb-1 mb-2">Categoria / Produtos</h3>
                
                <div class="flex items-center flex-wrap gap-2 mb-2">
                    <div class="flex flex-col">
                        @if(!empty($participant->ticket->modalidade))
                            <span class="text-[10px] font-bold text-gray-500 uppercase">{{ $participant->ticket->modalidade }}</span>
                        @endif
                        <span class="text-base font-extrabold text-gray-900 uppercase">{{ $participant->ticket->nome }}</span>
                    </div>
                    
                    @if($isMeiaIdoso)
                        <span class="bg-blue-100 text-blue-700 text-[10px] font-extrabold px-1.5 py-0.5 rounded uppercase tracking-wide">
                            Idoso (60+)
                        </span>
                    @endif
                </div>

                @php
                    $produtosArray = is_string($participant->produtos) ? json_decode($participant->produtos, true) : $participant->produtos;
                    $temKit = !empty($participant->kit);
                    $temProdutos = !empty($produtosArray) && is_array($produtosArray) && count($produtosArray) > 0;
                @endphp

                @if($temKit || $temProdutos)
                    <div class="text-sm text-gray-700 space-y-1 mt-2">
                        @if($temKit)
                            <p>Kit: <strong>{{ is_string($participant->kit) ? $participant->kit : ($participant->kit->nome ?? 'Kit Padrão') }}</strong></p>
                            @if(!empty($participant->tamanho_camiseta))
                                <p>Tamanho Camiseta: <strong>{{ $participant->tamanho_camiseta }}</strong></p>
                            @endif
                        @endif

                        @if($temProdutos)
                            @foreach($produtosArray as $produtoId => $atributoId)
                                @php
                                    $produtoBanco = \Illuminate\Support\Facades\DB::table('products')->where('id', $produtoId)->first();
                                    $tituloProduto = $produtoBanco ? $produtoBanco->titulo : "Produto #$produtoId";
                                    $tabelaAtributos = \Illuminate\Support\Facades\Schema::hasTable('product_attributes') ? 'product_attributes' : 'product_attribut';
                                    $atributoBanco = !empty($atributoId) ? \Illuminate\Support\Facades\DB::table($tabelaAtributos)->where('id', $atributoId)->first() : null;
                                    $nomeAtributo = $atributoBanco ? $atributoBanco->nome : '';
                                @endphp
                                <p>{{ $tituloProduto }}: <strong>{{ $nomeAtributo }}</strong></p>
                            @endforeach
                        @endif
                    </div>
                @endif
            </div>

            <!-- EXTRATO DE PAGAMENTO -->
            @if($order)
            <div>
                <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-1 mb-2">Pagamento</h3>
                <div class="text-sm text-gray-600 space-y-1 bg-gray-50/50 p-3 print:p-2 rounded-xl border border-gray-100">
                    <p class="flex justify-between">
                        <span class="text-gray-500">Data:</span> 
                        <strong>{{ $order->updated_at ? \Carbon\Carbon::parse($order->updated_at)->format('d/m/Y H:i') : \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</strong>
                    </p>
                    <p class="flex justify-between">
                        <span class="text-gray-500">Tipo:</span> 
                        <strong class="uppercase">{{ str_replace('_', ' ', $order->forma_pagamento ?? 'N/A') }}</strong>
                    </p>
                    <p class="flex justify-between mt-1 pt-1 border-t border-gray-200">
                        <span class="text-gray-800 font-bold">Valor Total:</span> 
                        <strong class="text-green-700 text-base">R$ {{ number_format($order->valor_total ?? 0, 2, ',', '.') }}</strong>
                    </p>
                </div>
            </div>
            @endif

            <!-- QR CODE -->
            <div class="flex flex-col items-center justify-center pt-3 pb-1 print:pt-2 border-t border-dashed border-gray-300">
                <div id="qrcode" class="p-3 bg-white border border-gray-200 rounded-2xl shadow-sm inline-block"></div>
                <p class="text-xs font-bold text-gray-500 mt-2 text-center uppercase tracking-wider">Apresente na entrada</p>
            </div>

        </div>

    </div>

    <!-- BOTÕES DE AÇÃO FLUTUANTES -->
    <div class="mt-6 flex gap-4 print:hidden">
        <button onclick="window.print()" class="bg-gray-900 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:bg-black transition flex items-center mx-auto">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0v3.396c0 .61.49 1.104 1.104 1.104h8.292a1.104 1.104 0 0 0 1.104-1.104V7.524Z" />
            </svg>
            Imprimir Ingresso
        </button>
    </div>

    <!-- MUDANÇAS DRÁSTICAS NO CSS PARA IMPRESSÃO -->
    <style>
        @media print {
            @page {
                size: auto;
                margin: 0mm; /* Mata as margens padrão do navegador */
            }
            body, html {
                margin: 0 !important;
                padding: 0 !important;
                height: 100% !important;
                background-color: white !important; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
            }
            .ticket-container {
                margin: 0 !important;
                padding: 10px !important;
                border: none !important;
                box-shadow: none !important;
                width: 100% !important;
                max-width: 100% !important;
                page-break-inside: avoid;
            }
        }
    </style>

    <script type="text/javascript">
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ route('checkin.validar', ['id' => $participant->id, 'documento' => $participant->cpf ?: $participant->passaporte]) }}",
            width: 140, /* Reduzi levemente o QR Code para garantir espaço na página */
            height: 140,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.M
        });
    </script>
</body>
</html>