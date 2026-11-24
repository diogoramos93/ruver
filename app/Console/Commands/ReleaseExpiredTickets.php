<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredTickets extends Command
{
    protected $signature = 'tickets:release-expired';
    protected $description = 'Cancela pedidos pendentes (15 min PIX/Cartão ou 3 dias Boleto) e devolve os ingressos para o estoque';

    public function handle()
    {
        $this->info("Iniciando a faxina de ingressos expirados...");

        // 1. Busca pedidos de PIX ou CARTÃO pendentes há mais de 15 minutos
        // Já carrega participants.ticket e ticket na memória para performance máxima
        $expiredFastOrders = Order::with(['participants.ticket', 'ticket'])
            ->where('status', 'pendente')
            ->where(function ($query) {
                $query->whereIn('forma_pagamento', ['PIX', 'CREDIT_CARD'])
                      ->orWhereNull('forma_pagamento');
            })
            ->where('created_at', '<', now()->subMinutes(15))
            ->get();

        // 2. Busca pedidos de BOLETO pendentes há mais de 3 DIAS
        $expiredBoletoOrders = Order::with(['participants.ticket', 'ticket'])
            ->where('status', 'pendente')
            ->where('forma_pagamento', 'BOLETO')
            ->where('created_at', '<', now()->subDays(3))
            ->get();

        // 3. Junta as duas listas numa só
        $expiredOrders = $expiredFastOrders->merge($expiredBoletoOrders);

        if ($expiredOrders->isEmpty()) {
            $this->info("Nenhum pedido expirado encontrado. O estoque está seguro.");
            return;
        }

        $count = 0;

        foreach ($expiredOrders as $order) {
            DB::beginTransaction();

            try {
                // Cancela o pedido
                $order->update(['status' => 'cancelado']);

                // Devolve os ingressos para o estoque por participante (suporta múltiplos lotes/tipos)
                if ($order->participants->isNotEmpty()) {
                    foreach ($order->participants as $participant) {
                        if ($participant->ticket && $participant->ticket->quantidade_total > 0) {
                            $participant->ticket->increment('quantidade_disponivel', 1);
                        }
                    }
                } elseif ($order->ticket && $order->ticket->quantidade_total > 0) {
                    // Fallback de segurança para pedidos antigos que não tenham participantes vinculados
                    $order->ticket->increment('quantidade_disponivel', $order->quantidade);
                }

                DB::commit();
                $count++;
                
                $forma = $order->forma_pagamento ?? 'DESCONHECIDA';
                Log::info("Pedido #{$order->id} (Pagamento: {$forma}) cancelado por tempo expirado. Estoque devolvido.");

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Erro ao cancelar o pedido #{$order->id}: " . $e->getMessage());
            }
        }

        $this->info("Faxina concluída: {$count} pedidos expirados foram cancelados e devolvidos ao estoque.");
    }
}