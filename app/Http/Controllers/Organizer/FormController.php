<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Organizer;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FormController extends Controller
{
    /** Identifica o organizador autenticado. */
    private function organizerId(): int
    {
        return Organizer::where('user_id', auth()->id())->firstOrFail()->id;
    }

    /** Garante que o evento pertence ao organizador autenticado. */
    private function findEvent(int $event): Event
    {
        return Event::where('organizer_id', $this->organizerId())
            ->findOrFail($event);
    }

    /** Garante que o formulário pertence ao evento e ao organizador. */
    private function findForm(Event $event, int $form): Form
    {
        return Form::where('organizer_id', $event->organizer_id)
            ->where('event_id', $event->id)
            ->findOrFail($form);
    }

    /** Lista os formulários do evento. */
    public function index(int $event)
    {
        $event = $this->findEvent($event);

        $forms = Form::where('organizer_id', $event->organizer_id)
            ->where('event_id', $event->id)
            ->withCount('fields')
            ->latest()
            ->paginate(15);

        return view('organizer.forms.index', compact('event', 'forms'));
    }

    /** Exibe a tela de criação. */
    public function create(int $event)
    {
        $event = $this->findEvent($event);

        $tickets = Ticket::where('event_id', $event->id)
            ->orderBy('nome')
            ->get();

        return view('organizer.forms.create', compact('event', 'tickets'));
    }

    /** Salva um formulário com suas perguntas e ingressos. */
    public function store(Request $request, int $event)
    {
        $event = $this->findEvent($event);
        $data = $this->validateForm($request, $event);

        DB::transaction(function () use ($event, $data) {
            $form = Form::create([
                'organizer_id' => $event->organizer_id,
                'event_id' => $event->id,
                'nome' => $data['nome'],
                'descricao' => $data['descricao'] ?? null,
                'ativo' => $data['ativo'] ?? true,
                'ordem' => 0,
            ]);

            $this->saveFields($form, $data['fields'] ?? []);
            $this->syncTickets($form, $data['tickets'] ?? []);
        });

        return redirect()
            ->route('organizer.events.forms.index', $event->id)
            ->with('success', 'Formulário criado com sucesso!');
    }

    /** Exibe a tela de edição. */
    public function edit(int $event, int $form)
    {
        $event = $this->findEvent($event);
        $form = $this->findForm($event, $form);
        $form->load(['fields', 'tickets']);

        $tickets = Ticket::where('event_id', $event->id)
            ->orderBy('nome')
            ->get();

        return view('organizer.forms.edit', compact('event', 'form', 'tickets'));
    }

    /** Atualiza o formulário, suas perguntas e seus ingressos. */
    public function update(Request $request, int $event, int $form)
    {
        $event = $this->findEvent($event);
        $form = $this->findForm($event, $form);
        $data = $this->validateForm($request, $event, $form);

        DB::transaction(function () use ($form, $data) {
            $form->update([
                'nome' => $data['nome'],
                'descricao' => $data['descricao'] ?? null,
                'ativo' => $data['ativo'] ?? true,
            ]);

            $this->saveFields($form, $data['fields'] ?? []);
            $this->syncTickets($form, $data['tickets'] ?? []);
        });

        return redirect()
            ->route('organizer.events.forms.index', $event->id)
            ->with('success', 'Formulário atualizado com sucesso!');
    }

    /** Ativa ou desativa o formulário. */
    public function toggle(int $event, int $form)
    {
        $event = $this->findEvent($event);
        $form = $this->findForm($event, $form);

        $form->update(['ativo' => !$form->ativo]);

        return back()->with('success', 'Status do formulário atualizado!');
    }

    /** Exclui o formulário. As respostas mantêm os snapshots salvos. */
    public function destroy(int $event, int $form)
    {
        $event = $this->findEvent($event);
        $form = $this->findForm($event, $form);
        $form->delete();

        return redirect()
            ->route('organizer.events.forms.index', $event->id)
            ->with('success', 'Formulário excluído!');
    }

    /** Valida formulário, perguntas e ingressos do evento atual. */
    private function validateForm(Request $request, Event $event, ?Form $form = null): array
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'descricao' => ['nullable', 'string'],
            'ativo' => ['sometimes', 'boolean'],
            'fields' => ['sometimes', 'array'],
            'fields.*.id' => ['nullable', 'integer'],
            'fields.*.titulo' => ['required', 'string', 'max:255'],
            'fields.*.descricao' => ['nullable', 'string'],
            'fields.*.tipo' => ['required', Rule::in(array_keys(FormField::TIPOS))],
            'fields.*.obrigatorio' => ['sometimes', 'boolean'],
            'fields.*.ativo' => ['sometimes', 'boolean'],
            'fields.*.placeholder' => ['nullable', 'string', 'max:255'],
            'fields.*.opcoes' => ['nullable', 'array'],
            'fields.*.opcoes.*' => ['required', 'string', 'max:255'],
            'fields.*.configuracoes' => ['nullable', 'array'],
            'tickets' => ['sometimes', 'array'],
            'tickets.*' => [
                'integer',
                'distinct',
                Rule::exists('tickets', 'id')->where('event_id', $event->id),
            ],
        ]);

        // Não permite reutilizar IDs de perguntas de outros formulários.
        $fieldIds = collect($data['fields'] ?? [])
            ->pluck('id')
            ->filter()
            ->values();

        if ($fieldIds->isNotEmpty()) {
            $validCount = $form
                ? $form->fields()->whereIn('id', $fieldIds)->count()
                : 0;

            if (
                $validCount !== $fieldIds->unique()->count()
                || $fieldIds->count() !== $fieldIds->unique()->count()
            ) {
                throw ValidationException::withMessages([
                    'fields' => 'Uma ou mais perguntas são inválidas.',
                ]);
            }
        }

        return $data;
    }

    /** Cria, atualiza e remove perguntas, respeitando a ordem enviada. */
    private function saveFields(Form $form, array $fields): void
    {
        $keptIds = [];

        foreach ($fields as $index => $fieldData) {
            $attributes = [
                'titulo' => $fieldData['titulo'],
                'descricao' => $fieldData['descricao'] ?? null,
                'tipo' => $fieldData['tipo'],
                'opcoes' => $fieldData['opcoes'] ?? null,
                'obrigatorio' => $fieldData['obrigatorio'] ?? false,
                'ativo' => $fieldData['ativo'] ?? true,
                'ordem' => $index,
                'placeholder' => $fieldData['placeholder'] ?? null,
                'configuracoes' => $fieldData['configuracoes'] ?? null,
            ];

            if (!empty($fieldData['id'])) {
                $field = $form->fields()->findOrFail($fieldData['id']);
                $field->update($attributes);
            } else {
                $field = $form->fields()->create($attributes);
            }

            $keptIds[] = $field->id;
        }

        $query = $form->fields();

        if (!empty($keptIds)) {
            $query->whereNotIn('id', $keptIds);
        }

        $query->delete();
    }

    /** Associa o formulário a vários ingressos do mesmo evento. */
    private function syncTickets(Form $form, array $ticketIds): void
    {
        $syncData = [];

        foreach ($ticketIds as $index => $ticketId) {
            $syncData[$ticketId] = [
                'ordem' => $index,
                'ativo' => true,
            ];
        }

        $form->tickets()->sync($syncData);
    }
}
