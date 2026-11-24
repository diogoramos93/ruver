<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookAsaasController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Pega os dados do Asaas e o Token de segurança do cabeçalho
        $payload = $request->all();
        $tokenEnviadoPeloAsaas = $request->header('asaas-access-token');

        // Salva no log para auditoria
        Log::info('Webhook Asaas Recebido:', ['event' => $payload['event'] ?? 'Nenhum', 'id' => $payload['payment']['id'] ?? 'Sem ID']);

        if (isset($payload['event'])) {
            $evento = $payload['event'];
            
            // Ignora evento de exclusão de token para não dar erro
            if ($evento === 'ACCESS_TOKEN_DELETED') {
                return response()->json(['status' => 'ignorado'], 200);
            }
            
            // 2. Trava de segurança: Verifica se tem os dados do pagamento
            if (isset($payload['payment'])) {
                
                $asaasPaymentId = $payload['payment']['id'] ?? null;
                $orderId = $payload['payment']['externalReference'] ?? null;

                // 3. Busca o pedido trazendo o organizador dono do evento para validar a senha
                $order = Order::with(['participants.ticket', 'ticket.event.organizer'])
                            ->where('asaas_payment_id', $asaasPaymentId)
                            ->orWhere('id', $orderId)
                            ->first();

                if ($order) {
                    $organizer = $order->ticket->event->organizer ?? null;

                    // 4. VALIDAÇÃO DE SEGURANÇA
                    if (!$organizer || empty($organizer->asaas_webhook_token) || $tokenEnviadoPeloAsaas !== $organizer->asaas_webhook_token) {
                        Log::warning("🚨 TENTATIVA DE FRAUDE OU TOKEN INVÁLIDO! Pedido #{$order->id}. Token Enviado: {$tokenEnviadoPeloAsaas}");
                        
                        return response()->json(['error' => 'Token de autenticação inválido ou não configurado'], 403);
                    }

                    // 5. Cliente PAGOU
                    if ($evento === 'PAYMENT_RECEIVED' || $evento === 'PAYMENT_CONFIRMED') {
                        if ($order->status !== 'pago') {
                            $order->update(['status' => 'pago']);
                            Log::info("✅ Webhook Asaas: Pedido #{$order->id} marcado como PAGO com sucesso!");
                        }
                    }
                    
                    // 6. Pagamento FALHOU (Boleto venceu, Pix expirou, Cartão estornado)
                    elseif (in_array($evento, ['PAYMENT_DELETED', 'PAYMENT_OVERDUE', 'PAYMENT_REFUNDED'])) {
                        if ($order->status !== 'pago' && $order->status !== 'cancelado') {
                            $order->update(['status' => 'cancelado']);
                            
                            // Devolve os ingressos travados de volta para o estoque
                            foreach ($order->participants as $participant) {
                                $ticket = $participant->ticket;
                                if ($ticket && $ticket->quantidade_total > 0) {
                                    $ticket->increment('quantidade_disponivel');
                                }
                            }
                            Log::info("❌ Webhook Asaas: Pedido #{$order->id} CANCELADO e estoque devolvido.");
                        }
                    }
                } else {
                    Log::warning("⚠️ Webhook Asaas: Pedido não encontrado para a cobrança {$asaasPaymentId}.");
                }
            }
        }

        // 7. Retorna 200 OK para o Asaas não bloquear a fila de envios
        return response()->json(['status' => 'sucesso'], 200);
    }
}