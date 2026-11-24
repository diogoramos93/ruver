<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Organizer;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Tenta buscar o organizador pela relação, pelo ID ou pelo E-mail
        $organizer = $user->organizer ?? 
                     Organizer::find($user->organizer_id) ?? 
                     Organizer::where('email', $user->email)->first();

        // Se não encontrar nenhum (usuário novo/sem vínculo), cria uma instância vazia para a tela não quebrar
        if (!$organizer) {
            $organizer = new Organizer();
        }

        return view('organizer.settings.index', compact('organizer'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $organizer = $user->organizer ?? 
                     Organizer::find($user->organizer_id) ?? 
                     Organizer::where('email', $user->email)->first();

        // Define o ID dinamicamente para o Unique ignorar o registro atual se ele já existir
        $organizerId = ($organizer && $organizer->exists) ? $organizer->id : null;

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'ano_criacao' => 'nullable|string|max:4',
            'bio' => 'nullable|string',
            'facebook' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:25',
            'cor_marca' => 'nullable|string|max:20',
            // 🛡️ Validação inteligente: Garante que o domínio seja único, ignorando o próprio organizador
            'dominio' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('organizers', 'dominio')->ignore($organizerId),
            ],
            'mp_public_key' => 'nullable|string',
            'mp_access_token' => 'nullable|string',
            'asaas_api_key' => 'nullable|string',
            'asaas_wallet_id' => 'nullable|string',
            'asaas_webhook_token' => 'nullable|string',
            'foto' => 'nullable|image|max:2048',
            'logo' => 'nullable|image|max:2048',
            'icone' => 'nullable|image|max:1024',
        ], [
            'dominio.unique' => 'Este domínio personalizado já está sendo utilizado por outro organizador.'
        ]);

        // Regras de Upload de Imagens
        if ($request->hasFile('foto')) {
            if ($organizer && $organizer->foto) Storage::disk('public')->delete($organizer->foto);
            $validated['foto'] = $request->file('foto')->store('organizers/fotos', 'public');
        }
        if ($request->hasFile('logo')) {
            if ($organizer && $organizer->logo) Storage::disk('public')->delete($organizer->logo);
            $validated['logo'] = $request->file('logo')->store('organizers/logos', 'public');
        }
        if ($request->hasFile('icone')) {
            if ($organizer && $organizer->icone) Storage::disk('public')->delete($organizer->icone);
            $validated['icone'] = $request->file('icone')->store('organizers/icones', 'public');
        }

        // Se o organizador já existe no banco, atualiza. Se não, cria um novo e vincula ao usuário!
        if ($organizer && $organizer->exists) {
            $organizer->update($validated);
        } else {
            $validated['email'] = $user->email;
            $validated['slug'] = Str::slug($validated['nome'] . '-' . uniqid());
            
            $organizer = Organizer::create($validated);
            
            // Salva o ID do organizador no usuário
            $user->organizer_id = $organizer->id;
            $user->save();
        }

        return back()->with('success', 'Configurações atualizadas com sucesso!');
    }
}