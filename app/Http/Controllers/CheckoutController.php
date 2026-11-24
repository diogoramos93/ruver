<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Order;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\OrderParticipant;
use App\Models\FormAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckoutController extends Controller
{

    // 1. Exibe a tela de checkout montando o carrinho

    public function show(Request $request)

    {

        $requestedTickets = $request->input('tickets', []);

        $selectedTickets = array_filter($requestedTickets, function ($quantity) {

            return (int) $quantity > 0;
        });

        if (empty($selectedTickets)) {

            return redirect()->back()->with('error', 'Por favor, selecione pelo menos um ingresso para continuar.');
        }

        $ticketIds = array_keys($selectedTickets);

        $tickets = Ticket::with('event.organizer')

            ->whereIn('id', $ticketIds)

            ->get();

        // Carrega os formulários dinâmicos vinculados a cada ingresso

        foreach ($tickets as $ticket) {

            $formularios = $ticket->forms()

                ->where('forms.ativo', true)

                ->where('forms.event_id', $ticket->event_id)

                ->wherePivot('ativo', true)

                ->with(['fields' => function ($query) {

                    $query->where('ativo', true)

                        ->orderBy('ordem');
                }])

                ->get();

            $ticket->setRelation('formulariosCheckout', $formularios);
        }

        $event = $tickets->first()->event;

        $productIds = [];

        foreach ($tickets as $ticket) {

            if (!empty($ticket->produtos_vinculados)) {

                $vinculados = is_string($ticket->produtos_vinculados) ? json_decode($ticket->produtos_vinculados, true) : $ticket->produtos_vinculados;

                if (is_array($vinculados)) {

                    $productIds = array_merge($productIds, $vinculados);
                }
            }
        }

        $produtosVinculados = Product::with('attributes')->whereIn('id', array_unique($productIds))->get();

        return view('checkout', compact('tickets', 'selectedTickets', 'event', 'produtosVinculados'));
    }

    // 2. Processa, valida e salva a compra completa e os participantes

    public function store(Request $request)

    {

        $selectedTickets = $request->input('tickets', []);

        $participantesInput = $request->input('participantes', []);

        $cupomCodigo = $request->input('cupom_codigo');

        $formaPagamento = $request->input('forma_pagamento', 'PIX');

        if (empty($selectedTickets)) {

            return redirect()->back()->with('error', 'Nenhum ingresso selecionado.');
        }

        // --- NOVA TRAVA DE VALIDAÇÃO DO CARTÃO AQUI ---

        if ($formaPagamento === 'CREDIT_CARD') {

            $request->validate([

                'cartao_nome' => 'required|string',

                'cartao_numero' => 'required|string',

                'cartao_mes' => 'required|string|size:2',

                'cartao_ano' => 'required|string|size:4',

                'cartao_cvv' => 'required|string|min:3|max:4',

            ], [

                'cartao_nome.required' => 'O nome impresso no cartão é obrigatório.',

                'cartao_numero.required' => 'O número do cartão é obrigatório.',

                'cartao_mes.required' => 'O mês de validade é obrigatório.',

                'cartao_mes.size' => 'O mês deve ter 2 dígitos (ex: 09).',

                'cartao_ano.required' => 'O ano de validade é obrigatório.',

                'cartao_ano.size' => 'O ano deve ter 4 dígitos (ex: 2028).',

                'cartao_cvv.required' => 'O código de segurança (CVV) é obrigatório.',

            ]);
        }

        // --- FIM DA NOVA TRAVA ---

        // Validação rigorosa dos participantes

        foreach ($participantesInput as $ticketId => $participantesDoIngresso) {

            foreach ($participantesDoIngresso as $index => $dados) {

                // Regra: Precisa ter CPF OU Passaporte

                if (empty($dados['cpf']) && empty($dados['passaporte'])) {

                    return redirect()->back()->withInput()->with('error', "O participante nº {$index} precisa ter obrigatoriamente um CPF ou um Passaporte preenchido.");
                }
            }
        }

        // Valida as respostas dos formulários antes de criar qualquer cobrança no Asaas.
        $respostasInput = $request->input('respostas_formulario', []);
        $camposPorIngresso = [];
        $eventoDaCompra = null;

        foreach ($selectedTickets as $ticketId => $quantidade) {
            $ticket = Ticket::with('event')->findOrFail($ticketId);
            $quantidade = filter_var($quantidade, FILTER_VALIDATE_INT);
            $participantesDoIngresso = $participantesInput[$ticketId] ?? [];

            if ($quantidade === false || $quantidade < 1 || !is_array($participantesDoIngresso) || count($participantesDoIngresso) !== $quantidade) {
                return back()->withInput()->with('error', 'A quantidade de participantes não corresponde aos ingressos selecionados.');
            }

            if ($eventoDaCompra === null) {
                $eventoDaCompra = $ticket->event_id;
            } elseif ((int) $eventoDaCompra !== (int) $ticket->event_id) {
                return back()->withInput()->with('error', 'Não é possível comprar ingressos de eventos diferentes no mesmo pedido.');
            }

            $formularios = $ticket->forms()
                ->where('forms.ativo', true)
                ->where('forms.event_id', $ticket->event_id)
                ->wherePivot('ativo', true)
                ->with(['fields' => function ($query) {
                    $query->where('ativo', true)->orderBy('ordem');
                }])->get();

            $camposPorIngresso[$ticketId] = $formularios->flatMap(function ($formulario) {
                return $formulario->fields->map(function ($campo) use ($formulario) {
                    return ['formulario' => $formulario, 'campo' => $campo];
                });
            });

            foreach ($participantesDoIngresso as $indice => $dados) {
                if (!is_array($dados)) {
                    return back()->withInput()->with('error', 'Dados de participante inválidos.');
                }

                foreach ($camposPorIngresso[$ticketId] as $item) {
                    $campo = $item['campo'];

                    if ($campo->tipo === 'image') {
                        $arquivo = $request->file("respostas_formulario.{$ticketId}.{$indice}.{$campo->id}");
                        if ($campo->obrigatorio && !$arquivo) {
                            return back()->withInput()->with('error', "Envie o anexo de imagem para a pergunta '{$campo->titulo}' do participante {$indice}.");
                        }
                        if ($arquivo) {
                            if (!$arquivo->isValid()) {
                                return back()->withInput()->with('error', "Falha no envio da imagem para '{$campo->titulo}' do participante {$indice}.");
                            }
                            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
                            $extensao = strtolower($arquivo->getClientOriginalExtension());
                            if (!in_array($extensao, $extensoesPermitidas, true)) {
                                return back()->withInput()->with('error', "Formato de imagem inválido para '{$campo->titulo}'. Use JPG, PNG ou WEBP.");
                            }
                            if ($arquivo->getSize() > 2097152) { // 2MB
                                return back()->withInput()->with('error', "A imagem para '{$campo->titulo}' não pode ultrapassar 2MB.");
                            }
                        }
                        continue;
                    }

                    $resposta = $respostasInput[$ticketId][$indice][$campo->id] ?? null;
                    $opcoes = is_array($campo->opcoes) ? array_values($campo->opcoes) : [];
                    $preenchido = is_array($resposta)
                        ? count($resposta) > 0
                        : ($resposta !== null && trim((string) $resposta) !== '');

                    if ($campo->obrigatorio && !$preenchido) {
                        return back()->withInput()->with('error', "Preencha a pergunta '{$campo->titulo}' do participante {$indice}.");
                    }
                    if (!$preenchido) {
                        continue;
                    }

                    if ($campo->tipo === 'checkbox') {
                        if (!is_array($resposta) || count(array_diff($resposta, $opcoes)) > 0) {
                            return back()->withInput()->with('error', "Resposta inválida para '{$campo->titulo}'.");
                        }
                    } elseif (!is_scalar($resposta)) {
                        return back()->withInput()->with('error', "Resposta inválida para '{$campo->titulo}'.");
                    } elseif (in_array($campo->tipo, ['select', 'radio'], true) && !in_array((string) $resposta, array_map('strval', $opcoes), true)) {
                        return back()->withInput()->with('error', "Selecione uma opção válida para '{$campo->titulo}'.");
                    } elseif ($campo->tipo === 'boolean' && (string) $resposta !== '1') {
                        return back()->withInput()->with('error', "Resposta inválida para '{$campo->titulo}'.");
                    } elseif ($campo->tipo === 'email' && !filter_var($resposta, FILTER_VALIDATE_EMAIL)) {
                        return back()->withInput()->with('error', "Informe um e-mail válido para '{$campo->titulo}'.");
                    } elseif ($campo->tipo === 'number' && !is_numeric($resposta)) {
                        return back()->withInput()->with('error', "Informe um número válido para '{$campo->titulo}'.");
                    } elseif ($campo->tipo === 'date' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $resposta) || !checkdate((int) substr($resposta, 5, 2), (int) substr($resposta, 8, 2), (int) substr($resposta, 0, 4)))) {
                        return back()->withInput()->with('error', "Informe uma data válida para '{$campo->titulo}'.");
                    }
                }
            }
        }

        // Usamos Transaction do banco para garantir que se der erro em algo, nada é salvo pela metade

        DB::beginTransaction();

        try {

            $user = auth()->user();

            // Atualiza o perfil do usuário automaticamente caso ele tenha preenchido dados novos na vaga 1

            if (isset($participantesInput)) {

                $primeiroTicketId = array_key_first($participantesInput);

                $primeiroParticipante = $participantesInput[$primeiroTicketId] ?? null;

                if ($primeiroParticipante) {

                    $dadosUser = reset($primeiroParticipante);

                    $user->update([

                        'cpf' => $user->cpf ?: ($dadosUser['cpf'] ?? null),

                        'passaporte' => $user->passaporte ?: ($dadosUser['passaporte'] ?? null),

                        'data_nascimento' => $user->data_nascimento ?: ($dadosUser['data_nascimento'] ?? null),

                        'sexo' => $user->sexo ?: ($dadosUser['sexo'] ?? null),

                        'cidade' => $user->cidade ?: ($dadosUser['cidade'] ?? null),

                    ]);
                }
            }

            $subtotalIngressos = 0;

            $totalIngressos = 0;

            $taxaPlataformaTotal = 0;

            $taxaRepassadaAoCliente = 0;

            // 👇 INÍCIO DA REGRA DO IDOSO (Cálculo do Carrinho) 👇

            $ticketsModelos = [];

            foreach ($selectedTickets as $ticketId => $quantidade) {

                if ($quantidade <= 0) continue;

                $ticket = Ticket::with('event.organizer')->findOrFail($ticketId);

                $ticketsModelos[$ticketId] = $ticket;

                if ($ticket->quantidade_total > 0 && $quantidade > $ticket->quantidade_disponivel) {

                    throw new \Exception("Desculpe, o lote do ingresso '{$ticket->nome}' acabou ou não tem quantidade suficiente.");
                }

                $totalIngressos += $quantidade;

                $participantesDaqueleIngresso = $participantesInput[$ticketId] ?? [];

                $organizer = $ticket->event->organizer;

                // Agora iteramos sobre CADA participante para cobrar o valor individual

                foreach ($participantesDaqueleIngresso as $dadosP) {

                    $precoFinalParticipante = $ticket->preco;

                    // Verifica se o ingresso aceita desconto E se o participante preencheu a data

                    if ($ticket->aceita_desconto_idoso && !empty($dadosP['data_nascimento'])) {

                        // Calcula a idade no momento da compra

                        $idade = Carbon::parse($dadosP['data_nascimento'])->age;

                        if ($idade >= 60) {

                            // Concede os 50% de desconto legal no ingresso

                            $precoFinalParticipante = $ticket->preco / 2;
                        }
                    }

                    $subtotalIngressos += $precoFinalParticipante;

                    // Cálculo da taxa por participante deste ingresso
                    $taxaItem = 0;
                    if ($organizer->tipo_taxa_plataforma === 'fixo') {
                        $taxaItem = (float) $organizer->taxa_plataforma;
                    } else {
                        $taxaItem = $precoFinalParticipante * ((float) $organizer->taxa_plataforma / 100);
                    }

                    $taxaPlataformaTotal += $taxaItem;

                    if ($ticket->taxa_organizador !== 'absorver') {
                        $taxaRepassadaAoCliente += $taxaItem;
                    }
                }
            }

            // 👆 FIM DA REGRA DO IDOSO 👆

            // Calcula o valor dos produtos/kits selecionados por cada participante

            $totalProdutos = 0;

            foreach ($participantesInput as $ticketId => $participantesDoIngresso) {

                $ticket = $ticketsModelos[$ticketId] ?? Ticket::with('event.organizer')->find($ticketId);
                $organizer = $ticket ? $ticket->event->organizer : null;

                foreach ($participantesDoIngresso as $dadosP) {

                    if (isset($dadosP['produtos']) && is_array($dadosP['produtos'])) {

                        foreach ($dadosP['produtos'] as $prodId => $attrId) {

                            $attr = \App\Models\ProductAttribute::with('product')->find($attrId);

                            if ($attr) {

                                $precoAdicional = (float) $attr->preco_adicional;

                                $precoBase = $attr->product ? (float) $attr->product->preco_base : 0;

                                $valorProduto = $precoAdicional + $precoBase;

                                $totalProdutos += $valorProduto;

                                if ($organizer && $organizer->tipo_taxa_plataforma !== 'fixo') {
                                    $taxaProd = $valorProduto * ((float) $organizer->taxa_plataforma / 100);
                                    $taxaPlataformaTotal += $taxaProd;

                                    if ($ticket && $ticket->taxa_organizador !== 'absorver') {
                                        $taxaRepassadaAoCliente += $taxaProd;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Pega o organizador do primeiro ingresso para calcular a taxa da plataforma

            $primeiroTicket = $ticketsModelos[array_key_first($selectedTickets)] ?? Ticket::with('event.organizer')->find(array_key_first($selectedTickets));

            $organizer = $primeiroTicket->event->organizer;

            $totalTaxa = $taxaPlataformaTotal;

            $valorGeral = $subtotalIngressos + $totalProdutos + $taxaRepassadaAoCliente;

            // Aplicação de Cupom de Desconto (caso exista)

            $descontoAplicado = 0;

            if (!empty($cupomCodigo)) {

                $cupom = Coupon::where('codigo', $cupomCodigo)

                    ->where('event_id', $primeiroTicket->event_id)

                    ->where('ativo', true)

                    ->first();

                if (!$cupom) {

                    throw new \Exception('Cupom inválido ou inativo.');
                }

                $agora = now();

                if ($cupom->data_inicio && $agora->lt($cupom->data_inicio)) {

                    throw new \Exception('Este cupom ainda não está válido.');
                }

                if ($cupom->data_termino && $agora->gt($cupom->data_termino)) {

                    throw new \Exception('Este cupom já expirou.');
                }

                if ($cupom->quantidade_total !== null && $cupom->quantidade_usada >= $cupom->quantidade_total) {

                    throw new \Exception('Este cupom está esgotado.');
                }

                if ($cupom->tipo_desconto === 'porcentagem') {

                    $descontoAplicado = $subtotalIngressos * ($cupom->valor_desconto / 100);
                } else {

                    $descontoAplicado = $cupom->valor_desconto;
                }

                $descontoAplicado = min($descontoAplicado, $valorGeral);

                $valorGeral -= $descontoAplicado;

                // Incrementa o uso do cupom

                $cupom->increment('quantidade_usada');
            }

            $primeiroTicketIdKey = array_key_first($selectedTickets);

            $order = Order::create([

                'user_id' => $user->id,

                'ticket_id' => $primeiroTicketIdKey,

                'quantidade' => $totalIngressos,

                'valor_total' => max(0, $valorGeral),

                'status' => 'pendente',

                'forma_pagamento' => $formaPagamento,

            ]);

            // Integração do Asaas com Split de Pagamento da plataforma por organizador

            $asaasService = new \App\Services\AsaasPaymentService();

            $primeiroParticipanteDados = reset($participantesInput[$primeiroTicketIdKey]);

            // Captura os dados do cartão caso o usuário tenha escolhido cartão

            $dadosCartao = [];

            if ($formaPagamento === 'CREDIT_CARD') {

                $dadosCartao = [

                    'holderName' => $request->input('cartao_nome'),

                    'number' => $request->input('cartao_numero'),

                    'expiryMonth' => $request->input('cartao_mes'),

                    'expiryYear' => $request->input('cartao_ano'),

                    'ccv' => $request->input('cartao_cvv'),

                ];
            }

            $splitTaxa = min((float) $totalTaxa, (float) $order->valor_total);

            $pagamentoResultado = $asaasService->createPayment(

                $order,

                $organizer,

                [

                    'name' => $primeiroParticipanteDados['nome'] ?? $user->name,

                    'cpf' => $primeiroParticipanteDados['cpf'] ?? $user->cpf,

                    'email' => $user->email,

                    'phone' => $primeiroParticipanteDados['telefone'] ?? '00000000000', // default caso não exista telefone

                ],

                $splitTaxa,

                $formaPagamento,

                $dadosCartao

            );

            $order->update([

                'asaas_payment_id' => $pagamentoResultado['payment_id'] ?? null,

                'pix_copia_cola'   => $pagamentoResultado['pix_copia_cola'] ?? null,

                'bank_slip_url'    => $pagamentoResultado['bank_slip_url'] ?? null,

            ]);

            // Salva cada participante na nova tabela `order_participants` e baixa o estoque

            foreach ($selectedTickets as $ticketId => $quantidade) {

                if ($quantidade <= 0) continue;

                $ticket = Ticket::findOrFail($ticketId);

                $participantesDaqueleIngreso = $participantesInput[$ticketId] ?? [];

                foreach ($participantesDaqueleIngreso as $indice => $dadosP) {

                    // 👇 INÍCIO DA REGRA DO IDOSO (Salvando a flag no ingresso gerado) 👇

                    $valorPago = $ticket->preco;

                    $meiaIdoso = false;

                    if ($ticket->aceita_desconto_idoso && !empty($dadosP['data_nascimento'])) {

                        $idade = Carbon::parse($dadosP['data_nascimento'])->age;

                        if ($idade >= 60) {

                            $valorPago = $ticket->preco / 2;

                            $meiaIdoso = true;
                        }
                    }

                    // 👆 FIM DA REGRA DO IDOSO 👆

                    $participanteSalvo = OrderParticipant::create([

                        'order_id' => $order->id,

                        'ticket_id' => $ticket->id,

                        'nome' => $dadosP['nome'],

                        'cpf' => $dadosP['cpf'] ?? null,

                        'passaporte' => $dadosP['passaporte'] ?? null,

                        'data_nascimento' => $dadosP['data_nascimento'],

                        'sexo' => $dadosP['sexo'],

                        'cidade' => $dadosP['cidade'],

                        'equipe' => $dadosP['equipe'] ?? null,

                        'produtos' => $dadosP['produtos'] ?? null,

                        // Se você quiser no futuro exibir no PDF do ingresso, 

                        // pode adicionar campos como 'valor_pago' => $valorPago e 'is_meia_idoso' => $meiaIdoso na Migration dessa tabela

                    ]);
// 👇 INÍCIO DA BAIXA DE ESTOQUE DOS PRODUTOS 👇
                    if (isset($dadosP['produtos']) && is_array($dadosP['produtos'])) {
                        foreach ($dadosP['produtos'] as $prodId => $attrId) {
                            if ($attrId) {
                                $atributo = \App\Models\ProductAttribute::find($attrId);
                                
                                // Verifica se o estoque NÃO é nulo (ou seja, é limitado) e se é maior que 0
                                if ($atributo && !is_null($atributo->estoque) && $atributo->estoque > 0) {
                                    $atributo->decrement('estoque', 1);
                                }
                            }
                        }
                    }
                    // 👆 FIM DA BAIXA DE ESTOQUE DOS PRODUTOS 👆


                    // Grava as respostas de cada participante, mantendo snapshots históricos.
                    foreach ($camposPorIngresso[$ticketId] ?? [] as $item) {
                        $campo = $item['campo'];
                        $formulario = $item['formulario'];

                        if ($campo->tipo === 'image') {
                            $arquivo = $request->file("respostas_formulario.{$ticketId}.{$indice}.{$campo->id}");
                            $resposta = ($arquivo && $arquivo->isValid())
                                ? $arquivo->store('respostas_formularios', 'public')
                                : null;
                        } else {
                            $resposta = $respostasInput[$ticketId][$indice][$campo->id] ?? null;
                        }

                        if ($resposta === null || $resposta === '' || $resposta === []) {
                            continue;
                        }

                        $participanteSalvo->formAnswers()->create([
                            'form_id' => $formulario->id,
                            'form_field_id' => $campo->id,
                            'pergunta' => $campo->titulo,
                            'tipo' => $campo->tipo,
                            'resposta' => $resposta,
                            'configuracoes_snapshot' => [
                                'descricao' => $campo->descricao,
                                'opcoes' => $campo->opcoes,
                                'obrigatorio' => (bool) $campo->obrigatorio,
                                'placeholder' => $campo->placeholder,
                                'configuracoes' => $campo->configuracoes,
                            ],
                        ]);
                    }
                }

                if ($ticket->quantidade_total > 0) {

                    $ticket->decrement('quantidade_disponivel', $quantidade);
                }
            }

            DB::commit();

            return redirect()->route('order.payment', $order->id)->with('success', 'Pedido gerado com sucesso! Complete a transferência/pagamento abaixo.');
        } catch (\Illuminate\Validation\ValidationException $e) {

            DB::rollBack();

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    // 3. Valida o cupom via AJAX

    public function validarCupom(Request $request)

    {

        $request->validate([

            'codigo' => 'required|string',

            'event_id' => 'required|integer',

            'valor_total' => 'required|numeric'

        ]);

        $cupom = Coupon::where('codigo', $request->codigo)

            ->where('event_id', $request->event_id)

            ->where('ativo', true)

            ->first();

        if (!$cupom) {

            return response()->json(['error' => 'Cupom inválido ou inativo.'], 400);
        }

        $agora = now();

        if ($cupom->data_inicio && $agora->lt($cupom->data_inicio)) {

            return response()->json(['error' => 'Este cupom ainda não está válido.'], 400);
        }

        if ($cupom->data_termino && $agora->gt($cupom->data_termino)) {

            return response()->json(['error' => 'Este cupom já expirou.'], 400);
        }

        if ($cupom->quantidade_total !== null && $cupom->quantidade_usada >= $cupom->quantidade_total) {

            return response()->json(['error' => 'Este cupom está esgotado.'], 400);
        }

        $desconto = 0;

        if ($cupom->tipo_desconto === 'porcentagem') {

            $desconto = $request->valor_total * ($cupom->valor_desconto / 100);
        } else {

            $desconto = $cupom->valor_desconto;
        }

        $desconto = min($desconto, $request->valor_total);

        return response()->json([

            'success' => true,

            'desconto' => $desconto,

            'mensagem' => 'Cupom aplicado com sucesso!'

        ]);
    }

    // 4. Exibe o ingresso individual com QR Code (BLINDADO E COMPARTILHÁVEL 🔒📲)

    public function showTicket(Request $request, OrderParticipant $participant)

    {

        $participant->load('order.user', 'ticket.event.organizer');

        $order = $participant->order;

        // 1. TRAVA FINANCEIRA: O ingresso só abre se o pedido estiver PAGO

        if ($order->status !== 'pago') {

            return redirect()->route('home')

                ->with('error', 'Este ingresso ainda está aguardando confirmação de pagamento.');
        }

        // 2. TRAVA DE DONO OU CONVIDADO (URLs Assinadas)

        $user = auth()->user();

        $isOwner = $user && $order->user_id === $user->id;

        $isStaff = $user && in_array($user->role, ['admin', 'organizador']);

        // Verifica se o link tem a assinatura criptografada do Laravel (Para o amigo que recebeu no WhatsApp)

        $hasValidSignature = $request->hasValidSignature();

        if (!$isOwner && !$isStaff && !$hasValidSignature) {

            abort(403, 'Acesso Negado: O link deste ingresso é inválido ou você não tem permissão para visualizá-lo.');
        }

        return view('ticket-show', compact('participant'));
    }

    // 5. Exibe a tela interativa de pagamento do pedido

    public function paymentScreen(Order $order)

    {

        if ($order->user_id !== auth()->id()) {

            abort(403, 'Acesso não autorizado ao pedido.');
        }

        $order->load('ticket.event.organizer', 'participants');

        $formaPagamento = $order->forma_pagamento;

        $pixCopiaCola = $order->pix_copia_cola;

        $bankSlipUrl = $order->bank_slip_url;

        return view('order-payment', compact('order', 'formaPagamento', 'pixCopiaCola', 'bankSlipUrl'));
    }

    // 6. Retorna o status atual do pedido para o robozinho da tela de pagamento

    public function checkStatus(Order $order)

    {

        if ($order->user_id !== auth()->id()) {

            return response()->json(['error' => 'Não autorizado'], 403);
        }

        return response()->json([

            'status' => $order->status

        ]);
    }
}
