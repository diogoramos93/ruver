<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    @php
        $organizer = request()->get('currentOrganizer');

        $brandLogo = ($organizer && $organizer->logo) 
            ? asset('storage/' . $organizer->logo) 
            : asset('images/1-Ruver Logo.svg');

        $brandColor = ($organizer && $organizer->cor_marca) 
            ? $organizer->cor_marca 
            : '#000000';

        $brandIcon = ($organizer && $organizer->icone) 
            ? asset('storage/' . $organizer->icone) 
            : asset('images/favicon.png');
    @endphp

    <link rel="icon" type="image/png" href="{{ $brandIcon }}">

    <title>Finalizar Compra - {{ $event->titulo }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased flex flex-col min-h-screen">

    <nav class="shadow-md" style="background-color: {{ $brandColor }};">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <a href="{{ route('home') }}" class="flex items-center">
                <img src="{{ $brandLogo }}" alt="Logo" class="h-6 w-auto object-contain">
            </a>

            <div class="flex items-center space-x-6">
                <a href="{{ route('home') }}" class="text-sm font-semibold text-gray-300 hover:text-white transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Início
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full">
        
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900">Finalizar Inscrição</h1>
            <p class="text-gray-500 mt-1">{{ $event->titulo }}</p>
        </div>

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-2xl text-sm font-bold">
                {{ session('error') }}
            </div>
        @endif

        <form id="formCheckout" action="{{ route('checkout.store') }}" method="POST" enctype="multipart/form-data" onsubmit="return validarIdades(event)" class="lg:grid lg:grid-cols-12 lg:gap-10">
            @csrf
            
            <div class="lg:col-span-8 space-y-6">
                
                @php 
                    $participanteCount = 1; 
                    $user = auth()->user();
                @endphp

                @foreach($tickets as $ticket)
                    @php 
                        $quantidade = $selectedTickets[$ticket->id]; 
                        $vinculadosIds = is_string($ticket->produtos_vinculados) ? json_decode($ticket->produtos_vinculados, true) : $ticket->produtos_vinculados;
                        $vinculadosIds = is_array($vinculadosIds) ? $vinculadosIds : [];
                    @endphp

                    <input type="hidden" name="tickets[{{ $ticket->id }}]" value="{{ $quantidade }}">

                    @for($i = 1; $i <= $quantidade; $i++)
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden relative">
                            
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Participante {{ $participanteCount }}</h3>
                                    <p class="text-sm text-red-600 font-semibold">{{ $ticket->nome }}</p>
                                </div>
                                
                                @if($participanteCount === 1)
                                    <button type="button" 
                                            onclick="preencherMeusDados()" 
                                            class="text-xs bg-black text-white px-3 py-1.5 rounded-lg hover:bg-gray-800 transition font-bold shadow-sm flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 mr-1">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-5.5-2.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0ZM10 12a5.99 5.99 0 0 0-4.793 2.39A6.483 6.483 0 0 0 10 16.5a6.483 6.483 0 0 0 4.793-2.11A5.99 5.99 0 0 0 10 12Z" clip-rule="evenodd" />
                                        </svg>
                                        Sou eu
                                    </button>
                                @endif
                            </div>

                            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Nome Completo *</label>
                                    <input type="text" name="participantes[{{ $ticket->id }}][{{ $i }}][nome]" required 
                                           class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black" 
                                           id="nome_{{ $participanteCount }}" value="{{ $participanteCount === 1 ? $user->name : '' }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">CPF (Residente no Brasil)</label>
                                    <input type="text" name="participantes[{{ $ticket->id }}][{{ $i }}][cpf]" placeholder="000.000.000-00" 
                                           class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black" 
                                           id="cpf_{{ $participanteCount }}" value="{{ $participanteCount === 1 ? $user->cpf : '' }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Passaporte (Estrangeiro)</label>
                                    <input type="text" name="participantes[{{ $ticket->id }}][{{ $i }}][passaporte]" placeholder="Nº Passaporte" 
                                           class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black" 
                                           id="passaporte_{{ $participanteCount }}" value="{{ $participanteCount === 1 ? $user->passaporte : '' }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Data de Nascimento *</label>
                                    <input type="date" name="participantes[{{ $ticket->id }}][{{ $i }}][data_nascimento]" required 
                                           class="input-nascimento w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black" 
                                           id="nascimento_{{ $participanteCount }}" 
                                           value="{{ $participanteCount === 1 ? $user->data_nascimento?->format('Y-m-d') : '' }}"
                                           data-idade-minima="{{ $ticket->idade_minima ?? 0 }}"
                                           data-nome-ingresso="{{ $ticket->nome }}"
                                           data-preco="{{ $ticket->preco }}"
                                           data-taxa-organizador="{{ $ticket->taxa_organizador ?? 'repassar' }}"
                                           data-aceita-meia-idoso="{{ $ticket->aceita_desconto_idoso ? 'true' : 'false' }}"
                                           onchange="recalcularTotalCheckout()">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Sexo *</label>
                                    <select name="participantes[{{ $ticket->id }}][{{ $i }}][sexo]" required 
                                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black" 
                                            id="sexo_{{ $participanteCount }}">
                                        <option value="" disabled {{ $participanteCount === 1 && !$user->sexo ? 'selected' : '' }}>Selecione...</option>
                                        <option value="Masculino" {{ $participanteCount === 1 && $user->sexo === 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                        <option value="Feminino" {{ $participanteCount === 1 && $user->sexo === 'Feminino' ? 'selected' : '' }}>Feminino</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Cidade *</label>
                                    <input type="text" name="participantes[{{ $ticket->id }}][{{ $i }}][cidade]" required placeholder="Ex: Sombrio" 
                                           class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black" 
                                           id="cidade_{{ $participanteCount }}" value="{{ $participanteCount === 1 ? $user->cidade : '' }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Equipe / Assessoria <span class="text-gray-400 font-normal">(Opcional)</span></label>
                                    <input type="text" name="participantes[{{ $ticket->id }}][{{ $i }}][equipe]" placeholder="Nome da equipe" 
                                           class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black">
                                </div>

                                @if(count($vinculadosIds) > 0)
                                    <div class="sm:col-span-2 mt-4 space-y-3">
                                        <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2 border-b border-gray-100 pb-2">Itens do Kit do Atleta</h4>
                                        
                                        @foreach($produtosVinculados as $produto)
                                            @if(in_array($produto->id, $vinculadosIds))
                                                <div class="bg-gray-50/70 border border-gray-200 rounded-2xl p-3 flex flex-col sm:flex-row items-start sm:items-center gap-4 hover:border-black transition">
                                                    
                                                    <div class="w-full sm:w-28 h-40 sm:h-28 bg-white rounded-xl border border-gray-100 overflow-hidden shrink-0 flex items-center justify-center relative group">
                                                        @if(isset($produto->foto) && $produto->foto)
                                                            <img src="{{ asset('storage/' . $produto->foto) }}" alt="{{ $produto->titulo }}" 
                                                                 class="w-full h-full object-cover cursor-pointer group-hover:scale-110 transition-transform duration-300"
                                                                 onclick="abrirModalImagem('{{ asset('storage/' . $produto->foto) }}')"
                                                                 title="Clique para ampliar">
                                                            
                                                            <div class="absolute inset-0 bg-black/20 hidden group-hover:flex items-center justify-center pointer-events-none transition">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-6 h-6 drop-shadow-md">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" />
                                                                </svg>
                                                            </div>
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                            </svg>
                                                            <span class="absolute bottom-1.5 text-[9px] font-bold text-gray-400">SEM FOTO</span>
                                                        @endif
                                                    </div>

                                                    <div class="flex-1 w-full">
                                                        <!-- Título do produto com aviso de (Opcional) -->
                                                        <label class="block text-sm font-extrabold text-gray-900 mb-0.5">
                                                            {{ $produto->titulo }} <span class="text-gray-400 font-normal">(Opcional)</span>
                                                        </label>
                                                        
                                                        @if(isset($produto->descricao) && $produto->descricao)
                                                            <p class="text-[11px] text-gray-500 mb-2">{{ $produto->descricao }}</p>
                                                        @else
                                                            <p class="text-[11px] text-gray-500 mb-2">Item opcional. Escolha um tamanho se desejar.</p>
                                                        @endif
                                                        
                                                        <!-- Select SEM o 'required' 👇 -->
                                                        <select name="participantes[{{ $ticket->id }}][{{ $i }}][produtos][{{ $produto->id }}]" onchange="recalcularTotalCheckout()" class="select-produto-attr w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black text-sm bg-white font-semibold text-gray-700" data-taxa-organizador="{{ $ticket->taxa_organizador ?? 'repassar' }}">
                                                            
                                                            <!-- Opção padrão agora permite que o usuário não escolha nada -->
                                                            <option value="" data-preco="0" selected>Não desejo este item</option>
                                                            
                                                            @foreach($produto->attributes as $attr)
                                                                @php
                                                                    $precoTotalAttr = (float) $attr->preco_adicional + (float) ($produto->preco_base ?? 0);
                                                                    
                                                                    // Mantém a trava APENAS visual para o que estiver esgotado
                                                                    $esgotado = !is_null($attr->estoque) && $attr->estoque <= 0;
                                                                @endphp
                                                                
                                                                <option value="{{ $attr->id }}" data-preco="{{ $precoTotalAttr }}" {{ $esgotado ? 'disabled' : '' }}>
                                                                    {{ $attr->nome }} 
                                                                    @if($precoTotalAttr > 0) (+ R$ {{ number_format($precoTotalAttr, 2, ',', '.') }}) @endif
                                                                    @if($esgotado) (ESGOTADO) @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                {{-- FORMULÁRIOS DINÂMICOS DO INGRESSO --}}
                                @include('campos-formularios-checkout')

                            </div>
                        </div>
                        @php $participanteCount++; @endphp
                    @endfor
                @endforeach
            </div>


            <!-- COLUNA DIREITA: Resumo do Pedido -->
            <div class="lg:col-span-4 mt-8 lg:mt-0">
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 sticky top-10">
                    
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Resumo do Pedido</h3>

                    <div class="space-y-4 mb-6">
                        @php 
                            $totalIngressos = 0;
                            $subtotalIngressosBase = 0; 
                            
                            foreach($tickets as $ticket) {
                                $qtde = $selectedTickets[$ticket->id];
                                $totalIngressos += $qtde;
                                $subtotalIngressosBase += $qtde * $ticket->preco;
                            }
                        @endphp
                        
                        @foreach($tickets as $ticket)
                            @php 
                                $qtde = $selectedTickets[$ticket->id]; 
                            @endphp
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ $qtde }}x {{ $ticket->nome }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <hr class="border-gray-200 mb-6">

                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">Cupom de Desconto</label>
                        <div class="flex space-x-2 relative">
                            <input type="text" id="codigo_cupom" placeholder="Digite seu código" class="w-full text-sm rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black uppercase">
                            <button type="button" onclick="aplicarCupom()" class="bg-gray-900 hover:bg-black text-white px-4 rounded-xl text-sm font-bold transition">
                                Aplicar
                            </button>
                        </div>
                        <p id="msg_cupom" class="text-xs font-bold mt-2 hidden"></p>
                    </div>

                    <hr class="border-gray-200 mb-6">

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Subtotal Ingressos</span>
                            <span id="valor_subtotal_ingressos">R$ 0,00</span>
                        </div>

                        <div class="flex justify-between text-sm text-green-600 font-semibold hidden" id="linha_desconto_idoso">
                            <span>Desc. Idoso (50%)</span>
                            <span id="valor_desconto_idoso_texto">- R$ 0,00</span>
                        </div>

                        <div class="flex justify-between text-sm text-gray-600 hidden" id="linha_produtos">
                            <span>Produtos / Kits</span>
                            <span id="valor_produtos_texto">R$ 0,00</span>
                        </div>

                        <!-- ID linha_taxa ADICIONADO AQUI -->
                        <div class="flex justify-between text-sm text-gray-600 hidden" id="linha_taxa">
                            <span>Taxa da Plataforma</span>
                            <span id="valor_taxa_texto">R$ 0,00</span>
                        </div>
                        
                        <div class="flex justify-between text-sm text-green-600 font-semibold hidden" id="linha_desconto">
                            <span>Desconto Promocional</span>
                            <span id="valor_desconto_texto">- R$ 0,00</span>
                        </div>
                        <div class="flex justify-between text-xl font-extrabold text-gray-900 pt-2 border-t border-gray-100">
                            <span>Total</span>
                            <span id="valor_total_texto">R$ 0,00</span>
                        </div>
                    </div>

                    <!-- MÉTODOS DE PAGAMENTO DINÂMICOS -->
                    @php
                        $temPix = $event->aceita_pix;
                        $temCartao = $event->aceita_cartao;
                        $temBoleto = $event->aceita_boleto && (!$event->data_limite_boleto || now()->startOfDay()->lte($event->data_limite_boleto));
                        
                        $primeiroMetodo = $temPix ? 'PIX' : ($temCartao ? 'CREDIT_CARD' : ($temBoleto ? 'BOLETO' : null));
                    @endphp

                    @if($temPix || $temCartao || $temBoleto)
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Forma de Pagamento</label>
                            
                            <div class="grid grid-cols-{{ ($temPix ? 1 : 0) + ($temCartao ? 1 : 0) + ($temBoleto ? 1 : 0) }} gap-2">
                                
                                @if($temPix)
                                <label class="border-2 {{ $primeiroMetodo == 'PIX' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-800' : 'border-gray-200 bg-white text-gray-700 hover:border-black' }} p-3 rounded-2xl cursor-pointer flex flex-col items-center justify-center text-center font-bold text-xs transition option-forma-pagamento">
                                    <input type="radio" name="forma_pagamento" value="PIX" {{ $primeiroMetodo == 'PIX' ? 'checked' : '' }} class="sr-only" onchange="selecionarFormaPagamento(this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" class="w-6 h-6 mb-1 fill-current">
                                        <path d="M11.917 11.71a2.046 2.046 0 0 1-1.454-.602l-2.1-2.1a.4.4 0 0 0-.551 0l-2.108 2.108a2.044 2.044 0 0 1-1.454.602h-.414l2.66 2.66c.83.83 2.177.83 3.007 0l2.667-2.668h-.253zM4.25 4.282c.55 0 1.066.214 1.454.602l2.108 2.108a.39.39 0 0 0 .552 0l2.1-2.1a2.044 2.044 0 0 1 1.453-.602h.253L9.503 1.623a2.127 2.127 0 0 0-3.007 0l-2.66 2.66h.414z"/>
                                        <path d="m14.377 6.496-1.612-1.612a.307.307 0 0 1-.114.023h-.733c-.379 0-.75.154-1.017.422l-2.1 2.1a1.005 1.005 0 0 1-1.425 0L5.268 5.32a1.448 1.448 0 0 0-1.018-.422h-.9a.306.306 0 0 1-.109-.021L1.623 6.496c-.83.83-.83 2.177 0 3.008l1.618 1.618a.305.305 0 0 1 .108-.022h.901c.38 0 .75-.153 1.018-.421L7.375 8.57a1.034 1.034 0 0 1 1.426 0l2.1 2.1c.267.268.638.421 1.017.421h.733c.04 0 .079.01.114.024l1.612-1.612c.83-.83.83-2.178 0-3.008z"/>
                                    </svg>
                                    <span>PIX</span>
                                </label>
                                @endif

                                @if($temCartao)
                                <label class="border-2 {{ $primeiroMetodo == 'CREDIT_CARD' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-800' : 'border-gray-200 bg-white text-gray-700 hover:border-black' }} p-3 rounded-2xl cursor-pointer flex flex-col items-center justify-center text-center font-bold text-xs transition option-forma-pagamento">
                                    <input type="radio" name="forma_pagamento" value="CREDIT_CARD" {{ $primeiroMetodo == 'CREDIT_CARD' ? 'checked' : '' }} class="sr-only" onchange="selecionarFormaPagamento(this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-1 text-current">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                    </svg>
                                    <span>Cartão</span>
                                </label>
                                @endif

                                @if($temBoleto)
                                <label class="border-2 {{ $primeiroMetodo == 'BOLETO' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-800' : 'border-gray-200 bg-white text-gray-700 hover:border-black' }} p-3 rounded-2xl cursor-pointer flex flex-col items-center justify-center text-center font-bold text-xs transition option-forma-pagamento">
                                    <input type="radio" name="forma_pagamento" value="BOLETO" {{ $primeiroMetodo == 'BOLETO' ? 'checked' : '' }} class="sr-only" onchange="selecionarFormaPagamento(this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-1 text-current">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                                    </svg>
                                    <span>Boleto</span>
                                </label>
                                @endif
                            </div>
                            
                            <div id="container-cartao" class="{{ $primeiroMetodo == 'CREDIT_CARD' ? '' : 'hidden' }} mt-4 space-y-3 bg-gray-50 p-4 rounded-2xl border border-gray-200">
                                <h4 class="text-xs font-bold text-gray-700 uppercase">Dados do Cartão de Crédito</h4>
                                
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nome no Cartão *</label>
                                    <input type="text" name="cartao_nome" placeholder="Como impresso no cartão" {{ $primeiroMetodo == 'CREDIT_CARD' ? 'required' : '' }} class="w-full text-sm rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Número do Cartão *</label>
                                    <input type="text" name="cartao_numero" placeholder="0000 0000 0000 0000" maxlength="19" {{ $primeiroMetodo == 'CREDIT_CARD' ? 'required' : '' }} class="w-full text-sm rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black">
                                </div>

                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Mês (MM) *</label>
                                        <input type="text" name="cartao_mes" placeholder="MM" maxlength="2" {{ $primeiroMetodo == 'CREDIT_CARD' ? 'required' : '' }} class="w-full text-sm rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Ano (AAAA) *</label>
                                        <input type="text" name="cartao_ano" placeholder="AAAA" maxlength="4" {{ $primeiroMetodo == 'CREDIT_CARD' ? 'required' : '' }} class="w-full text-sm rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">CVV *</label>
                                        <input type="password" name="cartao_cvv" placeholder="123" maxlength="4" {{ $primeiroMetodo == 'CREDIT_CARD' ? 'required' : '' }} class="w-full text-sm rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="cupom_codigo" id="cupom_aplicado">
                        <input type="hidden" name="valor_total_pedido" value="0">

                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-4 rounded-2xl shadow-lg transition-all flex justify-center items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 mr-2">
                                <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                            </svg>
                            Ir para Pagamento
                        </button>
                        
                        <p class="text-center text-xs text-gray-400 mt-4">Ambiente 100% Seguro</p>
                    @else
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-2xl text-sm font-bold text-center">
                            As formas de pagamento estão indisponíveis no momento. Tente novamente mais tarde.
                        </div>
                        <button type="button" disabled class="w-full bg-gray-300 text-gray-500 font-bold py-4 px-4 rounded-2xl flex justify-center items-center cursor-not-allowed">
                            Pagamento Indisponível
                        </button>
                    @endif
                </div>
            </div>

        </form>
    </main>

    <footer class="bg-black text-gray-400 py-6 mt-12 border-t border-gray-900 text-xs mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-gray-300">&copy; {{ date('Y') }} {{ config('app.name', 'IngressosApp') }}. Todos os direitos reservados.</p>
        </div>
    </footer>

    <div id="modalImagemProduto" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity" onclick="fecharModalImagem()">
        <div class="relative max-w-3xl w-full flex justify-center" onclick="event.stopPropagation()">
            <button type="button" class="absolute -top-12 right-0 text-white hover:text-gray-300 transition focus:outline-none" onclick="fecharModalImagem()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-10 h-10 drop-shadow-md">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <img id="imagemAmpliadaSrc" src="" alt="Produto Ampliado" class="max-h-[85vh] max-w-full rounded-2xl shadow-2xl object-contain bg-white">
        </div>
    </div>

    <script>
        function abrirModalImagem(src) {
            const modal = document.getElementById('modalImagemProduto');
            const img = document.getElementById('imagemAmpliadaSrc');
            img.src = src;
            modal.classList.remove('hidden');
        }

        function fecharModalImagem() {
            const modal = document.getElementById('modalImagemProduto');
            modal.classList.add('hidden');
        }

        function preencherMeusDados() {
            const nome = "{{ $user->name ?? '' }}";
            const cpf = "{{ $user->cpf ?? '' }}";
            const passaporte = "{{ $user->passaporte ?? '' }}";
            const nascimento = "{{ $user->data_nascimento?->format('Y-m-d') ?? '' }}";
            const sexo = "{{ $user->sexo ?? '' }}";
            const cidade = "{{ $user->cidade ?? '' }}";
            
            if(document.getElementById('nome_1')) document.getElementById('nome_1').value = nome;
            if(document.getElementById('cpf_1')) document.getElementById('cpf_1').value = cpf;
            if(document.getElementById('passaporte_1')) document.getElementById('passaporte_1').value = passaporte;
            if(document.getElementById('nascimento_1')) document.getElementById('nascimento_1').value = nascimento;
            if(document.getElementById('sexo_1')) document.getElementById('sexo_1').value = sexo;
            if(document.getElementById('cidade_1')) document.getElementById('cidade_1').value = cidade;
            
            alert('Dados preenchidos com as informações do seu cadastro!');
            
            recalcularTotalCheckout(); 
        }

        function validarIdades(e) {
            const dateInputs = document.querySelectorAll('.input-nascimento');
            
            for(let input of dateInputs) {
                if(input.value) {
                    const dataNascimento = new Date(input.value);
                    const hoje = new Date();
                    
                    let idade = hoje.getFullYear() - dataNascimento.getFullYear();
                    const m = hoje.getMonth() - dataNascimento.getMonth();
                    
                    if (m < 0 || (m === 0 && hoje.getDate() < dataNascimento.getDate())) {
                        idade--;
                    }
                    
                    const idadeMinima = parseInt(input.getAttribute('data-idade-minima')) || 0;
                    const nomeIngresso = input.getAttribute('data-nome-ingresso');
                    
                    if(idadeMinima > 0 && idade < idadeMinima) {
                        e.preventDefault();
                        alert(`Atenção: A idade mínima para o ingresso "${nomeIngresso}" é de ${idadeMinima} anos. A data informada indica ${idade} anos.`);
                        input.focus();
                        return false;
                    }
                }
            }
            return true;
        }

        const totalIngressosConst = {{ $totalIngressos }};
        const tipoTaxaPlataforma = "{{ $event->organizer->tipo_taxa_plataforma }}";
        const taxaPlataformaValor = {{ (float) $event->organizer->taxa_plataforma }};
        const eventId = {{ $event->id }};
        const urlValidarCupom = "{{ route('checkout.cupom.validar') }}";
        const csrfToken = "{{ csrf_token() }}";

        let valorDescontoGlobal = 0;
        let subtotalComMeia = 0; 

        function calcularIdadeJS(dataISO) {
            if(!dataISO) return 0;
            const dataNascimento = new Date(dataISO);
            const hoje = new Date();
            let idade = hoje.getFullYear() - dataNascimento.getFullYear();
            const m = hoje.getMonth() - dataNascimento.getMonth();
            if (m < 0 || (m === 0 && hoje.getDate() < dataNascimento.getDate())) {
                idade--;
            }
            return idade;
        }

        function recalcularTotalCheckout() {
            let totalProdutos = 0;
            let novoSubtotalIngressos = 0; 
            let subtotalIngressosCheio = 0; 
            let totalDescontoIdoso = 0;
            let taxaTotal = 0; // Taxa calculada dinamicamente

            // 1. Recalcula Ingressos e aplica as regras de taxa INDIVIDUAIS
            document.querySelectorAll('.input-nascimento').forEach(input => {
                let precoBase = parseFloat(input.getAttribute('data-preco')) || 0;
                let precoFinal = precoBase;
                let aceitaIdoso = input.getAttribute('data-aceita-meia-idoso') === 'true';
                let taxaOrg = input.getAttribute('data-taxa-organizador'); // 'absorver' ou 'repassar'
                
                if (aceitaIdoso && input.value) {
                    let idade = calcularIdadeJS(input.value);
                    if (idade >= 60) {
                        precoFinal = precoBase / 2;
                        totalDescontoIdoso += (precoBase / 2);
                    }
                }
                
                subtotalIngressosCheio += precoBase;
                novoSubtotalIngressos += precoFinal;

                // NOVA REGRA DE TAXA: Só soma se não for "absorver"
                if (taxaOrg !== 'absorver') {
                    if (tipoTaxaPlataforma === 'fixo') {
                        taxaTotal += taxaPlataformaValor;
                    } else {
                        taxaTotal += precoFinal * (taxaPlataformaValor / 100);
                    }
                }
            });
            
            subtotalComMeia = novoSubtotalIngressos;

            document.getElementById('valor_subtotal_ingressos').textContent = `R$ ${subtotalIngressosCheio.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

            const linhaDescontoIdoso = document.getElementById('linha_desconto_idoso');
            const textoDescontoIdoso = document.getElementById('valor_desconto_idoso_texto');
            if (totalDescontoIdoso > 0) {
                linhaDescontoIdoso.classList.remove('hidden');
                textoDescontoIdoso.textContent = `- R$ ${totalDescontoIdoso.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            } else {
                linhaDescontoIdoso.classList.add('hidden');
            }

            // 2. Recalcula Produtos / Kits
            document.querySelectorAll('.select-produto-attr').forEach(select => {
                const selectedOpt = select.options[select.selectedIndex];
                let taxaOrg = select.getAttribute('data-taxa-organizador'); // 'absorver' ou 'repassar'

                if (selectedOpt && selectedOpt.dataset.preco) {
                    let precoProduto = parseFloat(selectedOpt.dataset.preco) || 0;
                    totalProdutos += precoProduto;

                    // Produtos vinculados ao ingresso também seguem a regra do ingresso
                    if (taxaOrg !== 'absorver' && tipoTaxaPlataforma !== 'fixo') {
                        taxaTotal += precoProduto * (taxaPlataformaValor / 100);
                    }
                }
            });

            const linhaProdutos = document.getElementById('linha_produtos');
            const valorProdutosTexto = document.getElementById('valor_produtos_texto');
            if (totalProdutos > 0) {
                linhaProdutos.classList.remove('hidden');
                valorProdutosTexto.textContent = `+ R$ ${totalProdutos.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            } else {
                linhaProdutos.classList.add('hidden');
            }

            // 3. Atualiza na tela a Taxa da Plataforma
            const linhaTaxa = document.getElementById('linha_taxa');
            if (taxaTotal > 0) {
                linhaTaxa.classList.remove('hidden');
                document.getElementById('valor_taxa_texto').textContent = `R$ ${taxaTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            } else {
                linhaTaxa.classList.add('hidden'); // Esconde a linha se a taxa cobrada for ZERO!
            }

            // 4. Calcula Total Final
            const subtotalComTudo = subtotalComMeia + totalProdutos + taxaTotal;
            const totalComDesconto = Math.max(0, subtotalComTudo - valorDescontoGlobal);

            document.getElementById('valor_total_texto').textContent = `R$ ${totalComDesconto.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            
            const inputHiddenValorTotal = document.querySelector('input[name="valor_total_pedido"]');
            if (inputHiddenValorTotal) inputHiddenValorTotal.value = totalComDesconto;
        }

        document.addEventListener('DOMContentLoaded', () => {
            recalcularTotalCheckout();
        });

        async function aplicarCupom() {
            const inputCodigo = document.getElementById('codigo_cupom').value.trim();
            const msgCupom = document.getElementById('msg_cupom');
            const linhaDesconto = document.getElementById('linha_desconto');
            const textoDesconto = document.getElementById('valor_desconto_texto');
            const inputHiddenCupom = document.getElementById('cupom_aplicado');

            if (!inputCodigo) {
                msgCupom.textContent = "Digite um código de cupom.";
                msgCupom.className = "text-xs font-bold mt-2 text-red-500 block";
                return;
            }

            recalcularTotalCheckout();

            let totalProdutos = 0;
            document.querySelectorAll('.select-produto-attr').forEach(select => {
                const selectedOpt = select.options[select.selectedIndex];
                if (selectedOpt && selectedOpt.dataset.preco) {
                    totalProdutos += parseFloat(selectedOpt.dataset.preco) || 0;
                }
            });
            
            const subtotalAtual = subtotalComMeia + totalProdutos;

            try {
                const response = await fetch(urlValidarCupom, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        codigo: inputCodigo,
                        event_id: eventId,
                        valor_total: subtotalAtual
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Erro ao validar cupom');
                }

                msgCupom.textContent = data.mensagem;
                msgCupom.className = "text-xs font-bold mt-2 text-green-600 block";
                
                valorDescontoGlobal = data.desconto;
                const descontoFormatado = valorDescontoGlobal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                linhaDesconto.classList.remove('hidden');
                textoDesconto.textContent = `- R$ ${descontoFormatado}`;
                
                inputHiddenCupom.value = inputCodigo;
                recalcularTotalCheckout();

            } catch (error) {
                msgCupom.textContent = error.message;
                msgCupom.className = "text-xs font-bold mt-2 text-red-500 block";
                linhaDesconto.classList.add('hidden');
                valorDescontoGlobal = 0;
                inputHiddenCupom.value = "";
                recalcularTotalCheckout();
            }
        }

        function selecionarFormaPagamento(input) {
            document.querySelectorAll('.option-forma-pagamento').forEach(label => {
                label.classList.remove('border-emerald-500', 'bg-emerald-50/50', 'text-emerald-800');
                label.classList.add('border-gray-200', 'bg-white', 'text-gray-700');
            });
            
            const selectedLabel = input.closest('label');
            selectedLabel.classList.remove('border-gray-200', 'bg-white', 'text-gray-700');
            selectedLabel.classList.add('border-emerald-500', 'bg-emerald-50/50', 'text-emerald-800');

            const containerCartao = document.getElementById('container-cartao');
            const inputsCartao = containerCartao.querySelectorAll('input');

            if (input.value === 'CREDIT_CARD') {
                containerCartao.classList.remove('hidden');
                inputsCartao.forEach(campo => campo.setAttribute('required', 'required'));
            } else {
                containerCartao.classList.add('hidden');
                inputsCartao.forEach(campo => campo.removeAttribute('required'));
            }
        }
    </script>
</body>
</html>