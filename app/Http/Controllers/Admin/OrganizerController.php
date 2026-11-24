<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organizer;
use App\Models\User;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrganizerController extends Controller
{
    // Lista todos os organizadores com busca e totais de eventos
    public function index(Request $request)
    {
        $query = Organizer::query();

        if ($request->filled('search')) {
            $busca = $request->search;
            $query->where(function($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%")
                  ->orWhere('cnpj', 'like', "%{$busca}%");
            });
        }

        $organizers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.organizers.index', compact('organizers'));
    }

    // Formulário para criar novo organizador
    public function create()
    {
        return view('admin.organizers.create');
    }

    // Salvar o organizador e o usuário no banco
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:organizers,email|unique:users,email',
            'cnpj' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'tipo_taxa_plataforma' => 'required|in:porcentagem,fixo',
            'taxa_plataforma' => 'required|numeric|min:0',
        ]);

        // Garante um slug 100% único para evitar conflito no banco de dados
        $baseSlug = Str::slug($request->nome);
        $slug = $baseSlug;
        
        while (Organizer::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . rand(1000, 9999);
        }

        DB::transaction(function () use ($request, $slug) {
            $user = User::create([
                'name' => $request->nome,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'organizador',
            ]);

            Organizer::create([
                'user_id' => $user->id,
                'nome' => $request->nome,
                'email' => $request->email,
                'cnpj' => $request->cnpj,
                'slug' => $slug,
                'ativo' => true,
                'tipo_taxa_plataforma' => $request->tipo_taxa_plataforma,
                'taxa_plataforma' => $request->taxa_plataforma,
            ]);
        });

        return redirect()->route('admin.organizers.index')->with('success', 'Organizador e usuário criados com sucesso!');
    }

    // Formulário de Edição
    public function edit(Organizer $organizer)
    {
        return view('admin.organizers.edit', compact('organizer'));
    }

    // Atualização de Organizador
    public function update(Request $request, Organizer $organizer)
    {
        $user = User::where('email', $organizer->email)->orWhere('id', $organizer->user_id)->first();
        $userIdToIgnore = $user ? $user->id : 0;

        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => "required|email|unique:organizers,email,{$organizer->id}|unique:users,email,{$userIdToIgnore}",
            'cnpj' => 'nullable|string|max:20',
            'ativo' => 'required|boolean',
            'tipo_taxa_plataforma' => 'required|in:porcentagem,fixo',
            'taxa_plataforma' => 'required|numeric|min:0',
            'password' => 'nullable|string|min:8',
        ]);

        $organizer->update([
            'nome' => $request->nome,
            'email' => $request->email,
            'cnpj' => $request->cnpj,
            'ativo' => $request->ativo,
            'tipo_taxa_plataforma' => $request->tipo_taxa_plataforma,
            'taxa_plataforma' => $request->taxa_plataforma,
        ]);

        // Atualiza também o usuário vinculado se existir
        $user = User::where('email', $organizer->getOriginal('email'))->orWhere('id', $organizer->user_id)->first();
        if ($user) {
            $userData = [
                'name' => $request->nome,
                'email' => $request->email,
            ];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $user->update($userData);
        }

        return redirect()->route('admin.organizers.index')->with('success', 'Organizador atualizado com sucesso!');
    }

    // Detalhamento Financeiro e Eventos do Organizador
    public function show(Organizer $organizer)
    {
        $events = Event::where('organizer_id', $organizer->id)->with('tickets')->latest()->get();
        $eventIds = $events->pluck('id');

        // Busca todas as vendas dos eventos deste organizador
        $ticketIds = \App\Models\Ticket::whereIn('event_id', $eventIds)->pluck('id');
        $participants = OrderParticipant::whereIn('ticket_id', $ticketIds)
            ->whereHas('order', function($q) {
                $q->where('status', 'pago');
            })
            ->with(['order', 'ticket'])
            ->get();

        $faturamentoTotal = 0;
        $taxaSuperAdminTotal = 0;
        $totalIngressosVendidos = $participants->count();

        foreach ($participants as $p) {
            $precoIngresso = $p->ticket ? (float)$p->ticket->preco : 0;
            $faturamentoTotal += $precoIngresso;

            if ($organizer->tipo_taxa_plataforma === 'porcentagem') {
                $taxaSuperAdminTotal += ($precoIngresso * ((float)$organizer->taxa_plataforma / 100));
            } else {
                $taxaSuperAdminTotal += (float)$organizer->taxa_plataforma;
            }
        }

        $repasseOrganizador = max(0, $faturamentoTotal - $taxaSuperAdminTotal);

        return view('admin.organizers.show', compact(
            'organizer',
            'events',
            'faturamentoTotal',
            'taxaSuperAdminTotal',
            'repasseOrganizador',
            'totalIngressosVendidos'
        ));
    }
}