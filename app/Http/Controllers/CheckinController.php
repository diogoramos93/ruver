<?php

namespace App\Http\Controllers;

use App\Models\OrderParticipant;
use App\Models\Organizer;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function validar($id, $documento)
    {
        // 1. Verifica se quem está lendo o QR Code tem permissão da portaria
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'organizador'])) {
            abort(403, 'Acesso Negado: Apenas a equipe do evento pode validar ingressos.');
        }

        // 2. Busca o ingresso e o pedido no banco
        $participant = OrderParticipant::with('order.ticket.event')->find($id);

        if (!$participant) {
            return response("<h1 style='color:red; text-align:center; font-family:sans-serif; margin-top:50px;'>❌ Ingresso não encontrado!</h1>", 404);
        }

        // 2.1. Se for organizador (não admin), valida se o evento pertence a ele
        if ($user->role === 'organizador') {
            $organizer = $user->organizer;
            if (!$organizer) {
                $organizer = Organizer::where('user_id', $user->id)->first();
            }
            if (!$organizer && !empty($user->email)) {
                $organizer = Organizer::where('email', $user->email)->first();
            }

            $eventOrganizerId = $participant->order?->ticket?->event?->organizer_id;

            if (!$organizer || !$eventOrganizerId || (int)$eventOrganizerId !== (int)$organizer->id) {
                return response("<h1 style='color:red; text-align:center; font-family:sans-serif; margin-top:50px;'>❌ Ingresso não encontrado ou sem permissão.</h1>", 403);
            }
        }

        // 3. Verifica se o documento (CPF ou Passaporte) bate com o QR Code
        $valido = false;
        if (!empty($participant->cpf)) {
            $cpfBanco = preg_replace('/\D/', '', $participant->cpf);
            $cpfUrl = preg_replace('/\D/', '', $documento);
            $valido = ($cpfBanco !== '' && $cpfBanco === $cpfUrl);
        } elseif (!empty($participant->passaporte)) {
            $passBanco = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $participant->passaporte));
            $passUrl = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $documento));
            $valido = ($passBanco !== '' && $passBanco === $passUrl);
        }

        if (!$valido) {
            return response("<h1 style='color:red; text-align:center; font-family:sans-serif; margin-top:50px;'>❌ Documento Inválido! Possível Fraude.</h1>", 403);
        }

        // 4. Verifica se o pedido realmente foi pago
        if ($participant->order->status !== 'pago') {
            return response("<h1 style='color:orange; text-align:center; font-family:sans-serif; margin-top:50px;'>⚠️ Pagamento Pendente!</h1>", 403);
        }

        // 5. Verifica se o ingresso JÁ FOI UTILIZADO
        if ($participant->checkin_at) {
            $dataCheckin = \Carbon\Carbon::parse($participant->checkin_at)->format('d/m/Y H:i');
            return response("<h1 style='color:red; text-align:center; font-family:sans-serif; margin-top:50px;'>❌ INGRESSO JÁ UTILIZADO!<br><br><small style='color:#666;'>Entrou em: {$dataCheckin}</small></h1>", 400);
        }

        // 6. TUDO CERTO! Dá baixa no ingresso salvando a hora atual
        $participant->update(['checkin_at' => now()]);

        $nomeEvento = $participant->order->ticket->event->titulo;
        return response("<h1 style='color:green; text-align:center; font-family:sans-serif; margin-top:50px;'>✅ ENTRADA LIBERADA!<br><br><small style='color:#333;'>{$participant->nome}</small><br><small style='color:#666; font-size:14px;'>{$nomeEvento}</small></h1>", 200);
    }
}