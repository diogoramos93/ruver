@extends('layouts.organizer')

@section('title', 'Criar formulário - ' . $event->titulo)

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">Gerenciar Evento: {{ $event->titulo }}</h2>
</div>

@include('organizer.partials.submenu')

<div class="mx-auto max-w-5xl py-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-2xl font-extrabold text-gray-900">Criar formulário</h3>
            <p class="mt-1 text-sm text-gray-500">Personalize as perguntas que serão respondidas pelos participantes.</p>
        </div>
        <a href="{{ route('organizer.events.forms.index', $event->id) }}" class="rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Voltar</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-800">
            <p class="font-bold">Confira os campos antes de salvar:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="form-builder" action="{{ route('organizer.events.forms.store', $event->id) }}" method="POST" class="space-y-6">
        @csrf

        <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <h4 class="mb-6 text-lg font-extrabold text-gray-900">Informações do formulário</h4>
            <div class="space-y-5">
                <div>
                    <label for="nome" class="mb-2 block text-sm font-semibold text-gray-700">Nome do formulário <span class="text-red-500">*</span></label>
                    <input id="nome" name="nome" type="text" maxlength="150" required value="{{ old('nome') }}" placeholder="Ex.: Informações adicionais do atleta" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-black focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <label for="descricao" class="mb-2 block text-sm font-semibold text-gray-700">Descrição (opcional)</label>
                    <textarea id="descricao" name="descricao" rows="3" placeholder="Explique o objetivo deste formulário" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-black focus:ring-1 focus:ring-black">{{ old('descricao') }}</textarea>
                </div>
                <label class="inline-flex cursor-pointer items-center gap-3 text-sm font-semibold text-gray-700">
                    <input type="hidden" name="ativo" value="0">
                    <input type="checkbox" name="ativo" value="1" {{ old('ativo', '1') == '1' ? 'checked' : '' }} class="h-5 w-5 rounded border-gray-300 accent-black">
                    Formulário ativo
                </label>
            </div>
        </section>

        <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h4 class="text-lg font-extrabold text-gray-900">Perguntas</h4>
                    <p class="mt-1 text-sm text-gray-500">Adicione as perguntas na ordem em que deverão aparecer.</p>
                </div>
                <button type="button" id="add-field" class="rounded-xl bg-black px-5 py-3 text-sm font-bold text-white transition hover:bg-gray-800">+ Adicionar pergunta</button>
            </div>
            <div id="fields-container" class="space-y-4"></div>
            <div id="empty-fields" class="rounded-2xl border-2 border-dashed border-gray-200 px-6 py-10 text-center text-sm text-gray-500">Nenhuma pergunta adicionada. Clique em <strong>Adicionar pergunta</strong> para começar.</div>
        </section>

        <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <h4 class="text-lg font-extrabold text-gray-900">Ingressos vinculados</h4>
            <p class="mb-5 mt-1 text-sm text-gray-500">Selecione os ingressos deste evento que utilizarão o formulário.</p>
            @if ($tickets->isEmpty())
                <div class="rounded-xl bg-gray-50 p-5 text-sm text-gray-600">Este evento ainda não possui ingressos. Você poderá vincular o formulário depois de cadastrar os ingressos.</div>
            @else
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($tickets as $ticket)
                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-gray-200 p-4 hover:border-gray-400">
                            <input type="checkbox" name="tickets[]" value="{{ $ticket->id }}" {{ in_array((string) $ticket->id, array_map('strval', old('tickets', [])), true) ? 'checked' : '' }} class="h-5 w-5 rounded border-gray-300 accent-black">
                            <span class="text-sm font-semibold text-gray-800">{{ $ticket->nome }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="flex flex-wrap justify-end gap-3 pb-8">
            <a href="{{ route('organizer.events.forms.index', $event->id) }}" class="rounded-xl border border-gray-200 bg-white px-6 py-3 font-bold text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="rounded-xl bg-black px-8 py-3 font-bold text-white transition hover:bg-gray-800">Salvar formulário</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('fields-container');
    const empty = document.getElementById('empty-fields');
    const addButton = document.getElementById('add-field');
    const form = document.getElementById('form-builder');
    const types = {
        text: 'Texto curto', textarea: 'Texto longo', number: 'Número',
        email: 'E-mail', phone: 'Telefone', date: 'Data',
        select: 'Lista de seleção', radio: 'Escolha única',
        checkbox: 'Múltipla escolha', boolean: 'Sim ou não',
        image: 'Anexo de Imagem (até 2MB)'
    };
    const optionTypes = new Set(['select', 'radio', 'checkbox']);
    let nextIndex = 0;

    function element(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    function makeInput(label, name, value, placeholder, multiline = false) {
        const wrapper = element('div');
        const labelNode = element('label', 'mb-2 block text-sm font-semibold text-gray-700', label);
        const input = document.createElement(multiline ? 'textarea' : 'input');
        if (!multiline) input.type = 'text';
        if (multiline) input.rows = 2;
        input.name = name;
        input.value = value ?? '';
        input.placeholder = placeholder;
        input.className = 'w-full rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-900 outline-none focus:border-black focus:ring-1 focus:ring-black';
        wrapper.append(labelNode, input);
        return { wrapper, input };
    }

    function addField(data = {}) {
        const index = nextIndex++;
        const prefix = `fields[${index}]`;
        const card = element('div', 'rounded-2xl border border-gray-200 bg-gray-50 p-5');
        const top = element('div', 'mb-5 flex items-center justify-between gap-3');
        const heading = element('h5', 'font-bold text-gray-900', 'Pergunta');
        const remove = element('button', 'rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-50', 'Remover');
        remove.type = 'button';
        remove.addEventListener('click', () => { card.remove(); refresh(); });
        top.append(heading, remove);

        const grid = element('div', 'grid gap-4 sm:grid-cols-2');
        const titleField = makeInput('Título da pergunta *', `${prefix}[titulo]`, data.titulo, 'Ex.: Qual é o tamanho da camiseta?');
        titleField.input.required = true;
        titleField.input.maxLength = 255;
        const typeWrapper = element('div');
        typeWrapper.append(element('label', 'mb-2 block text-sm font-semibold text-gray-700', 'Tipo de resposta *'));
        const select = element('select', 'w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none focus:border-black focus:ring-1 focus:ring-black');
        select.name = `${prefix}[tipo]`;
        for (const [key, label] of Object.entries(types)) {
            const option = document.createElement('option');
            option.value = key;
            option.textContent = label;
            select.append(option);
        }
        select.value = types[data.tipo] ? data.tipo : 'text';
        typeWrapper.append(select);
        grid.append(titleField.wrapper, typeWrapper);

        const description = makeInput('Descrição (opcional)', `${prefix}[descricao]`, data.descricao, 'Uma orientação para o participante', true);
        const placeholder = makeInput('Texto de exemplo (opcional)', `${prefix}[placeholder]`, data.placeholder, 'Ex.: Digite sua resposta');
        const optionsWrapper = element('div', 'space-y-2');
        const optionsTitle = element('label', 'block text-sm font-semibold text-gray-700', 'Opções de resposta');
        const optionsList = element('div', 'space-y-2');
        const addOption = element('button', 'rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-800 hover:bg-gray-100', '+ Adicionar opção');
        addOption.type = 'button';
        let nextOption = 0;
        function insertOption(value = '') {
            const optionIndex = nextOption++;
            const row = element('div', 'flex items-center gap-2');
            const input = element('input', 'min-w-0 flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm outline-none focus:border-black');
            input.type = 'text';
            input.name = `${prefix}[opcoes][${optionIndex}]`;
            input.placeholder = 'Digite uma opção';
            input.maxLength = 255;
            input.value = value;
            const deleteButton = element('button', 'rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-bold text-red-600', '×');
            deleteButton.type = 'button';
            deleteButton.setAttribute('aria-label', 'Remover opção');
            deleteButton.addEventListener('click', () => row.remove());
            row.append(input, deleteButton);
            optionsList.append(row);
        }
        addOption.addEventListener('click', () => insertOption());
        optionsWrapper.append(optionsTitle, optionsList, addOption);
        const existingOptions = Array.isArray(data.opcoes) ? data.opcoes : Object.values(data.opcoes || {});
        existingOptions.forEach(value => insertOption(value));
        if (!existingOptions.length) insertOption();
        function toggleOptions() {
            const show = optionTypes.has(select.value);
            optionsWrapper.hidden = !show;
            optionsWrapper.querySelectorAll('input').forEach(input => input.disabled = !show);
            optionsWrapper.classList.toggle('hidden', !show);
        }
        select.addEventListener('change', toggleOptions);
        toggleOptions();

        const flags = element('div', 'mt-5 flex flex-wrap gap-6');
        function flag(label, key, checked) {
            const wrapper = element('label', 'inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-gray-700');
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `${prefix}[${key}]`;
            hidden.value = '0';
            const input = document.createElement('input');
            input.type = 'checkbox';
            input.name = `${prefix}[${key}]`;
            input.value = '1';
            input.checked = checked;
            input.className = 'h-4 w-4 rounded border-gray-300 accent-black';
            wrapper.append(hidden, input, document.createTextNode(label));
            return wrapper;
        }
        flags.append(flag('Resposta obrigatória', 'obrigatorio', String(data.obrigatorio ?? '0') === '1'), flag('Pergunta ativa', 'ativo', String(data.ativo ?? '1') === '1'));

        card.append(top, grid);
        const extra = element('div', 'mt-4 grid gap-4 sm:grid-cols-2');
        extra.append(description.wrapper, placeholder.wrapper);
        card.append(extra, element('div', 'mt-4'));
        card.append(optionsWrapper, flags);
        container.append(card);
        refresh();
    }

    function refresh() {
        const cards = Array.from(container.children);
        empty.classList.toggle('hidden', cards.length > 0);
        cards.forEach((card, i) => { card.querySelector('h5').textContent = `Pergunta ${i + 1}`; });
    }

    addButton.addEventListener('click', () => addField());
    const previousFields = @json(old('fields', []));
    const oldFields = Array.isArray(previousFields) ? previousFields : Object.values(previousFields || {});
    oldFields.forEach(field => addField(field));

    form.addEventListener('submit', function (event) {
        for (const card of container.children) {
            const select = card.querySelector('select');
            if (!optionTypes.has(select.value)) continue;
            const inputs = Array.from(card.querySelectorAll('input[name*="[opcoes]"]')).filter(input => !input.disabled);
            const filled = inputs.filter(input => input.value.trim() !== '');
            if (!filled.length) {
                event.preventDefault();
                alert('Preencha pelo menos uma opção em cada pergunta de seleção.');
                inputs[0]?.focus();
                return;
            }
            inputs.forEach(input => { if (!input.value.trim()) input.disabled = true; });
        }
    });
});
</script>
@endsection
