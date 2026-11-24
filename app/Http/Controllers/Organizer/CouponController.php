<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Event $event)
    {
        $organizer = auth()->user()->organizer ?? \App\Models\Organizer::where('user_id', auth()->id())->orWhere('email', auth()->user()->email)->first();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            abort(403, 'Ação não autorizada.');
        }

        $coupons = Coupon::where('event_id', $event->id)->latest()->get();

        return view('organizer.coupons.index', compact('event', 'coupons'));
    }

    public function store(Request $request, Event $event)
    {
        $organizer = auth()->user()->organizer ?? \App\Models\Organizer::where('user_id', auth()->id())->orWhere('email', auth()->user()->email)->first();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            abort(403, 'Ação não autorizada.');
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'required|string|max:50|unique:coupons,codigo,NULL,id,event_id,' . $event->id,
            'tipo_desconto' => 'required|in:fixo,porcentagem',
            'valor_desconto' => 'required|numeric|min:0',
            'data_inicio' => 'required|date',
            'data_termino' => 'required|date|after_or_equal:data_inicio',
            'quantidade_total' => 'nullable|integer|min:1',
        ]);

        Coupon::create([
            'event_id' => $event->id,
            'nome' => $request->nome,
            'codigo' => strtoupper($request->codigo),
            'tipo_desconto' => $request->tipo_desconto,
            'valor_desconto' => $request->valor_desconto,
            'data_inicio' => $request->data_inicio,
            'data_termino' => $request->data_termino,
            'quantidade_total' => $request->quantidade_total,
            // Removida a linha do ativo, o banco já deixa 'true' (ativo) por padrão ao criar!
        ]);

        return redirect()->route('organizer.events.coupons.index', $event->id)
            ->with('success', 'Cupom de desconto criado com sucesso!');
    }

    // NOVA FUNÇÃO: Liga e desliga o cupom
    public function toggleActive(Event $event, Coupon $coupon)
    {
        $organizer = auth()->user()->organizer ?? \App\Models\Organizer::where('user_id', auth()->id())->orWhere('email', auth()->user()->email)->first();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            abort(403, 'Ação não autorizada.');
        }

        // Inverte o status atual (se tá true vira false, se tá false vira true)
        $coupon->update(['ativo' => !$coupon->ativo]);

        $status = $coupon->ativo ? 'ativado' : 'desativado';
        return back()->with('success', "Cupom {$status} com sucesso!");
    }

    public function destroy(Event $event, Coupon $coupon)
    {
        $organizer = auth()->user()->organizer ?? \App\Models\Organizer::where('user_id', auth()->id())->orWhere('email', auth()->user()->email)->first();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            abort(403, 'Ação não autorizada.');
        }

        $coupon->delete();

        return redirect()->route('organizer.events.coupons.index', $event->id)
            ->with('success', 'Cupom excluído com sucesso!');
    }
}