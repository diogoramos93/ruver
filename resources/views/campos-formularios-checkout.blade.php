{{-- Formulários dinâmicos do ingresso, por participante. --}}
@foreach(($ticket->relationLoaded('formulariosCheckout') ? $ticket->getRelation('formulariosCheckout') : ($ticket->activeForms ?? collect())) as $formulario)
    @if($formulario->fields->isNotEmpty())
        <section class="sm:col-span-2 mt-5 rounded-2xl border border-gray-200 bg-gray-50/70 p-5 space-y-4">
            <div class="border-b border-gray-200 pb-3">
                <h4 class="font-extrabold text-gray-900">{{ $formulario->nome }}</h4>
                @if($formulario->descricao)
                    <p class="mt-1 text-sm text-gray-500">{{ $formulario->descricao }}</p>
                @endif
            </div>
            @foreach($formulario->fields as $campo)
                @php
                    $nomeCampo = 'respostas_formulario[' . $ticket->id . '][' . $i . '][' . $campo->id . ']';
                    $valorAntigo = old('respostas_formulario.' . $ticket->id . '.' . $i . '.' . $campo->id);
                    $opcoes = is_array($campo->opcoes) ? array_values($campo->opcoes) : [];
                    $tipo = $campo->tipo;
                    $classe = 'w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black bg-white';
                @endphp
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-gray-700" for="campo_{{ $ticket->id }}_{{ $i }}_{{ $campo->id }}">
                        {{ $campo->titulo }}
                        @if($campo->obrigatorio)<span class="text-red-500">*</span>@endif
                    </label>
                    @if($campo->descricao)
                        <p class="text-xs text-gray-500">{{ $campo->descricao }}</p>
                    @endif
                    @if($tipo === 'textarea')
                        <textarea id="campo_{{ $ticket->id }}_{{ $i }}_{{ $campo->id }}" name="{{ $nomeCampo }}" rows="3" placeholder="{{ $campo->placeholder }}" @required($campo->obrigatorio) class="{{ $classe }}">{{ is_scalar($valorAntigo) ? $valorAntigo : '' }}</textarea>
                    @elseif($tipo === 'select')
                        <select id="campo_{{ $ticket->id }}_{{ $i }}_{{ $campo->id }}" name="{{ $nomeCampo }}" @required($campo->obrigatorio) class="{{ $classe }}">
                            <option value="">Selecione...</option>
                            @foreach($opcoes as $opcao)
                                <option value="{{ $opcao }}" @selected((string) $valorAntigo === (string) $opcao)>{{ $opcao }}</option>
                            @endforeach
                        </select>
                    @elseif($tipo === 'radio')
                        <div class="space-y-2">
                            @foreach($opcoes as $opcao)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="radio" name="{{ $nomeCampo }}" value="{{ $opcao }}" @checked((string) $valorAntigo === (string) $opcao) @required($campo->obrigatorio) class="text-black focus:ring-black border-gray-300">
                                    <span>{{ $opcao }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif($tipo === 'checkbox')
                        <div class="space-y-2">
                            @foreach($opcoes as $opcao)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="{{ $nomeCampo }}[]" value="{{ $opcao }}" @checked(in_array((string) $opcao, array_map('strval', is_array($valorAntigo) ? $valorAntigo : []), true)) class="rounded border-gray-300 text-black focus:ring-black">
                                    <span>{{ $opcao }}</span>
                                </label>
                            @endforeach
                            @if($campo->obrigatorio)
                                <p class="text-xs text-gray-500">Selecione pelo menos uma opção.</p>
                            @endif
                        </div>
                    @elseif($tipo === 'boolean')
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" id="campo_{{ $ticket->id }}_{{ $i }}_{{ $campo->id }}" name="{{ $nomeCampo }}" value="1" @checked((string) $valorAntigo === '1') @required($campo->obrigatorio) class="rounded border-gray-300 text-black focus:ring-black">
                            <span>Sim</span>
                        </label>
                    @elseif($tipo === 'image')
                        <input id="campo_{{ $ticket->id }}_{{ $i }}_{{ $campo->id }}" type="file" accept="image/png,image/jpeg,image/jpg,image/webp" name="{{ $nomeCampo }}" @required($campo->obrigatorio) class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-black file:text-white hover:file:bg-gray-800 focus:border-black focus:ring-black">
                        <p class="text-xs text-gray-500">Formatos aceitos: JPG, PNG, WEBP (Máx. 2MB).</p>
                    @else
                        <input id="campo_{{ $ticket->id }}_{{ $i }}_{{ $campo->id }}" type="{{ in_array($tipo, ['number', 'email', 'date'], true) ? $tipo : ($tipo === 'phone' ? 'tel' : 'text') }}" name="{{ $nomeCampo }}" value="{{ is_scalar($valorAntigo) ? $valorAntigo : '' }}" placeholder="{{ $campo->placeholder }}" @required($campo->obrigatorio) class="{{ $classe }}">
                    @endif
                </div>
            @endforeach
        </section>
    @endif
@endforeach
