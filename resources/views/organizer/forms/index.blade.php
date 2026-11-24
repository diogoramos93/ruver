@extends('layouts.organizer')

@section('title', 'Formulários - ' . $event->titulo)

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Gerenciar Evento: {{ $event->titulo }}</h2>
    </div>

    @include('organizer.partials.submenu')

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100 p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Formulários do evento</h3>
                <p class="text-sm text-gray-500 mt-1">Crie perguntas personalizadas e vincule formulários aos ingressos deste evento.</p>
            </div>
            <a href="{{ route('organizer.events.forms.create', $event->id) }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-black px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800 transition-colors">
                <span class="text-lg leading-none">+</span> Criar formulário
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($forms->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-6 py-14 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm">📋</div>
                <h4 class="text-base font-bold text-gray-900">Nenhum formulário cadastrado</h4>
                <p class="mx-auto mt-2 max-w-md text-sm text-gray-500">Este evento ainda não possui formulários. Crie um para solicitar informações adicionais dos participantes.</p>
                <a href="{{ route('organizer.events.forms.create', $event->id) }}"
                   class="mt-5 inline-flex items-center justify-center rounded-xl bg-black px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800 transition-colors">
                    Criar primeiro formulário
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-left">
                    <thead>
                        <tr class="text-xs uppercase tracking-wide text-gray-500">
                            <th class="py-3 pr-4 font-semibold">Formulário</th>
                            <th class="px-4 py-3 font-semibold">Perguntas</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="py-3 pl-4 text-right font-semibold">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($forms as $form)
                            <tr>
                                <td class="py-4 pr-4">
                                    <div class="font-semibold text-gray-900">{{ $form->nome }}</div>
                                    @if ($form->descricao)
                                        <p class="mt-1 max-w-lg text-sm text-gray-500">{{ $form->descricao }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">{{ $form->fields_count }}</td>
                                <td class="px-4 py-4">
                                    @if ($form->ativo)
                                        <span class="inline-flex rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">Ativo</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Inativo</span>
                                    @endif
                                </td>
                                <td class="py-4 pl-4">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('organizer.events.forms.edit', [$event->id, $form->id]) }}"
                                           class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition-colors">Editar</a>
                                        <form action="{{ route('organizer.events.forms.toggle', [$event->id, $form->id]) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition-colors">
                                                {{ $form->ativo ? 'Desativar' : 'Ativar' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('organizer.events.forms.destroy', [$event->id, $form->id]) }}" method="POST"
                                              onsubmit="return confirm('Excluir este formulário? Esta ação não poderá ser desfeita.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($forms->hasPages())
                <div class="mt-6">{{ $forms->links() }}</div>
            @endif
        @endif
    </div>
@endsection
