<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        @php
            // Resgata o organizador exclusivamente pelo domínio (Middleware), garantindo que no site principal seja Ruver
            $organizer = request()->get('currentOrganizer');

            // Define a logo: se o organizador tiver logo salva, usa ela; senão, usa a padrão da Ruver
            $brandLogo = ($organizer && $organizer->logo) 
                ? asset('storage/' . $organizer->logo) 
                : asset('images/1-Ruver Logo.svg');

            // Define a cor da barra (se o organizador tiver cor cadastrada, usa ela; senão, usa preto)
            $brandColor = ($organizer && $organizer->cor_marca) 
                ? $organizer->cor_marca 
                : '#000000';

            // Define o Favicon dinâmico do organizador ou usa o padrão da Ruver
            $brandIcon = ($organizer && $organizer->icone) 
                ? asset('storage/' . $organizer->icone) 
                : asset('images/favicon.png');
        @endphp

        <!-- Favicon Dinâmico (White-Label) -->
        <link rel="icon" type="image/png" href="{{ $brandIcon }}">

        <title>Resultados - {{ $event->titulo }} | {{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="bg-gray-50 text-gray-900 font-sans antialiased flex flex-col min-h-screen"
          x-data="{ 
              search: '', 
              sexoFiltro: 'todos', 
              modoVisualizacao: 'geral',
              modalAberto: false,
              atletaSelecionado: {} 
          }">

<!-- Navbar Dinâmica -->
<nav class="shadow-md relative z-40" style="background-color: {{ $brandColor }};">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
        <!-- Logo Dinâmica (White-Label) -->
        <a href="{{ route('home') }}" class="flex items-center">
            <img src="{{ $brandLogo }}" alt="Logo" class="h-6 w-auto object-contain">
        </a>

        <!-- LINKS DO TOPO (Apenas Computador) -->
        <div class="hidden lg:flex items-center">
            
            <!-- Link Início (Casinha) -->
            <a href="{{ route('home') }}" class="flex items-center text-sm font-semibold text-gray-300 hover:text-white mr-6 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Início
            </a>

            <!-- Link Resultados -->
            <a href="{{ route('resultados.index') }}" class="flex items-center text-sm font-semibold text-gray-300 hover:text-white mr-6 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                </svg>
                Resultados
            </a>

            @auth
                <a href="{{ route('my.tickets') }}" class="flex items-center text-sm font-semibold text-gray-300 hover:text-white mr-6 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                    </svg>
                    Meus Ingressos
                </a>
                <a href="{{ route('dashboard') }}" class="flex items-center text-sm font-semibold text-gray-300 hover:text-white mr-6 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    Meu Painel
                </a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-300 hover:text-white transition">Entrar</a>
            @endauth
        </div>
    </div>
</nav>

        <!-- Conteúdo Principal -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex-grow w-full">
            
            <div class="mb-8">
                <a href="{{ route('event.show', $event->slug) }}" class="inline-flex items-center text-sm text-gray-500 hover:text-black mb-4 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                    </svg>
                    Voltar para o evento
                </a>
                <h1 class="text-3xl font-bold uppercase tracking-tight text-gray-900">Resultados Oficiais</h1>
                <h2 class="text-lg font-medium text-gray-600 mt-1">{{ (string)($conteudoWeclex['nom'] ?? $event->titulo) }}</h2>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                @if($conteudoWeclex)
                    
                    <!-- Informações Gerais do Evento -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-100 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div><strong class="text-gray-500 block">Organizador:</strong> {{ (string)($conteudoWeclex['organisateur'] ?? 'N/D') }}</div>
                        <div><strong class="text-gray-500 block">Data da Prova:</strong> {{ (string)($conteudoWeclex['dates'] ?? 'N/D') }}</div>
                        <div><strong class="text-gray-500 block">Status:</strong> Resultados Oficializados</div>
                    </div>

                    @php
                        $todosAtletasLista = $atletas ?? [];
                        $gruposCategorias = [];

                        foreach ($todosAtletasLista as $item) {
                            $gruposCategorias[$item['cat']][] = $item;
                        }
                        ksort($gruposCategorias);
                        $totalGeral = count($todosAtletasLista);
                    @endphp

                    <!-- BARRA DE FILTROS -->
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 mb-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">🔍 Buscar Atleta</label>
                            <input type="text" x-model="search" placeholder="Nome ou Nº do atleta..." class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-black">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">🚻 Filtrar por Sexo</label>
                            <select x-model="sexoFiltro" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-black">
                                <option value="todos">Todos os Sexos</option>
                                <option value="M">Masculino (M)</option>
                                <option value="F">Feminino (F)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">🏆 Premiação</label>
                            <select x-model="modoVisualizacao" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-black">
                                <option value="geral">Geral (Mostrar Tudo)</option>
                                <option value="categoria">Por Categoria (Mostrar Cards)</option>
                            </select>
                        </div>
                    </div>

                    <!-- MODO 1: VISUALIZAÇÃO GERAL -->
                    <div x-show="modoVisualizacao === 'geral'" class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                        <div class="bg-black text-white px-5 py-3 flex justify-between items-center">
                            <h3 class="text-sm font-extrabold uppercase tracking-wider">
                                Lista Geral de Atletas
                            </h3>
                            <span class="text-xs bg-gray-800 text-gray-300 px-2.5 py-1 rounded-full">
                                {{ $totalGeral }} atletas
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="text-gray-400 border-b border-gray-200 text-xs uppercase bg-gray-50">
                                        <th class="py-3 px-4 font-semibold">Nº Peito</th>
                                        <th class="py-3 px-4 font-semibold">Atleta</th>
                                        <th class="py-3 px-4 font-semibold">Sexo</th>
                                        <th class="py-3 px-4 font-semibold">Modalidade</th>
                                        <th class="py-3 px-4 font-semibold">Categoria</th>
                                        <th class="py-3 px-4 font-semibold">Ano</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @if(count($todosAtletasLista) > 0)
                                        @foreach($todosAtletasLista as $index => $atleta)
                                            <tr class="hover:bg-gray-50 cursor-pointer transition"
                                                @click="atletaSelecionado = { 
                                                    n: @js($atleta['n']), 
                                                    d: @js($atleta['d']), 
                                                    x: @js($atleta['x']), 
                                                    a: @js($atleta['a']), 
                                                    p: @js($atleta['p']), 
                                                    cat: @js($atleta['cat']), 
                                                    pos: @js($tipoImportacao === 'racetag' ? ($atleta['pos_geral'] ?? '-') : ($index + 1)),
                                                    total: @js($tipoImportacao === 'racetag' ? ($atleta['total_geral'] ?? 0) : $totalGeral),
                                                    liquido: @js($atleta['liquido']),
                                                    bruto: @js($atleta['bruto']),
                                                    pace: @js($atleta['pace']),
                                                    vel: @js($atleta['vel'])
                                                }; modalAberto = true;"
                                                x-show="(sexoFiltro === 'todos' || sexoFiltro === @js($atleta['x'])) &&
                                                        (search === '' || 
                                                         @js(strtolower($atleta['n'])).includes(search.toLowerCase()) || 
                                                         @js(strtolower($atleta['d'])).includes(search.toLowerCase()))">
                                                <td class="py-3 px-4 font-mono font-bold">{{ $atleta['d'] !== '' ? $atleta['d'] : '-' }}</td>
                                                <td class="py-3 px-4 font-semibold">{{ $atleta['n'] }}</td>
                                                <td class="py-3 px-4">{{ $atleta['x'] !== '' ? $atleta['x'] : '-' }}</td>
                                                <td class="py-3 px-4"><span class="bg-gray-100 px-2.5 py-1 rounded-md text-xs font-bold">{{ $atleta['p'] }}</span></td>
                                                <td class="py-3 px-4"><span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-1 rounded">{{ $atleta['cat'] }}</span></td>
                                                <td class="py-3 px-4">{{ $atleta['a'] !== '' ? $atleta['a'] : '-' }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="p-4 text-center text-gray-500">Nenhum atleta encontrado.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- MODO 2: VISUALIZAÇÃO POR CARDS DE CATEGORIA -->
                    <div x-show="modoVisualizacao === 'categoria'" style="display: none;" class="space-y-8">
                        @if(count($gruposCategorias) > 0)
                            @foreach($gruposCategorias as $catNome => $atividades)
                                @php $totalCat = count($atividades); @endphp
                                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                                    
                                    <!-- Cabeçalho do Card da Categoria -->
                                    <div class="bg-black text-white px-5 py-3 flex justify-between items-center">
                                        <h3 class="text-sm font-extrabold uppercase tracking-wider">
                                            Categoria - {{ $catNome }}
                                        </h3>
                                        <span class="text-xs bg-gray-800 text-gray-300 px-2.5 py-1 rounded-full">
                                            {{ $totalCat }} atletas
                                        </span>
                                    </div>

                                    <!-- Tabela do Card com Posicionamento -->
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left border-collapse text-sm">
                                            <thead>
                                                <tr class="text-gray-400 border-b border-gray-200 text-xs uppercase bg-gray-50">
                                                    <th class="py-3 px-4 font-semibold">Classificação</th>
                                                    <th class="py-3 px-4 font-semibold">Nº Peito</th>
                                                    <th class="py-3 px-4 font-semibold">Atleta</th>
                                                    <th class="py-3 px-4 font-semibold">Sexo</th>
                                                    <th class="py-3 px-4 font-semibold">Modalidade</th>
                                                    <th class="py-3 px-4 font-semibold">Ano</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($atividades as $index => $atleta)
                                                    <tr class="hover:bg-gray-50 cursor-pointer transition"
                                                        @click="atletaSelecionado = { 
                                                            n: @js($atleta['n']), 
                                                            d: @js($atleta['d']), 
                                                            x: @js($atleta['x']), 
                                                            a: @js($atleta['a']), 
                                                            p: @js($atleta['p']), 
                                                            cat: @js($catNome), 
                                                            pos: @js($tipoImportacao === 'racetag' ? ($atleta['pos_categoria'] ?? '-') : ($index + 1)),
                                                            total: {{ $totalCat }},
                                                            liquido: @js($atleta['liquido']),
                                                            bruto: @js($atleta['bruto']),
                                                            pace: @js($atleta['pace']),
                                                            vel: @js($atleta['vel'])
                                                        }; modalAberto = true;"
                                                        x-show="(sexoFiltro === 'todos' || sexoFiltro === @js($atleta['x'])) &&
                                                                (search === '' || 
                                                                 @js(strtolower($atleta['n'])).includes(search.toLowerCase()) || 
                                                                 @js(strtolower($atleta['d'])).includes(search.toLowerCase()))">
                                                        
                                                        <td class="py-3 px-4 font-bold text-indigo-600">
                                                            @php
                                                                $posCategoria = $tipoImportacao === 'racetag'
                                                                    ? ($atleta['pos_categoria'] ?? null)
                                                                    : ($index + 1);
                                                            @endphp
                                                            @if($tipoImportacao === 'racetag' && $posCategoria === null)
                                                                @if(!empty($atleta['pos_geral']))
                                                                    Geral: {{ $atleta['pos_geral'] }}º
                                                                @else
                                                                    Sem resultado
                                                                @endif
                                                            @elseif($posCategoria == 1) 🥇 1º Lugar
                                                            @elseif($posCategoria == 2) 🥈 2º Lugar
                                                            @elseif($posCategoria == 3) 🥉 3º Lugar
                                                            @else {{ $posCategoria }}º Lugar
                                                            @endif
                                                        </td>
                                                        <td class="py-3 px-4 font-mono font-bold text-gray-700">{{ $atleta['d'] !== '' ? $atleta['d'] : '-' }}</td>
                                                        <td class="py-3 px-4 font-bold text-gray-900">{{ $atleta['n'] }}</td>
                                                        <td class="py-3 px-4 text-gray-600">{{ $atleta['x'] !== '' ? $atleta['x'] : '-' }}</td>
                                                        <td class="py-3 px-4"><span class="bg-gray-100 px-2 py-1 rounded text-xs font-bold">{{ $atleta['p'] }}</span></td>
                                                        <td class="py-3 px-4 text-gray-600">{{ $atleta['a'] !== '' ? $atleta['a'] : '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-center text-gray-500 py-8">Nenhum atleta encontrado.</p>
                        @endif
                    </div>

                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <h3 class="text-xl font-bold text-gray-700">Arquivo não encontrado ou inválido</h3>
                        <p class="text-gray-500 mt-2">Confira se a pasta do evento contém um arquivo .clac, .clax ou results.json válido.</p>
                    </div>
                @endif
            </div>

        </main>

        <!-- MODAL DE DETALHES DO ATLETA -->
        <div x-show="modalAberto" 
             style="display: none;" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
             x-transition.opacity>
            
            <div @click.away="modalAberto = false" class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 relative border border-gray-100">
                
                <button @click="modalAberto = false" class="absolute top-4 right-4 text-gray-400 hover:text-black transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="text-center mb-6">
                    <span class="bg-black text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider" x-text="'Categoria - ' + atletaSelecionado.cat"></span>
                    <h3 class="text-2xl font-black text-gray-900 mt-3" x-text="atletaSelecionado.n"></h3>
                    <p class="text-xs font-bold text-gray-400 mt-1 uppercase">Nº de Peito: <span class="text-black font-mono text-sm" x-text="atletaSelecionado.d || '-'"></span></p>
                    
                    <div class="mt-3 inline-block bg-indigo-50 text-indigo-700 font-black text-lg px-4 py-1.5 rounded-xl border border-indigo-100" x-show="atletaSelecionado.pos !== '-'">
                        <span x-text="atletaSelecionado.pos + 'º / ' + atletaSelecionado.total"></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center">
                        <span class="block text-xs font-bold uppercase text-gray-400">Líquido</span>
                        <span class="text-lg font-black text-gray-900 font-mono" x-text="atletaSelecionado.liquido"></span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center">
                        <span class="block text-xs font-bold uppercase text-gray-400">Bruto</span>
                        <span class="text-lg font-black text-gray-900 font-mono" x-text="atletaSelecionado.bruto"></span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center">
                        <span class="block text-xs font-bold uppercase text-gray-400">Pace</span>
                        <span class="text-sm font-bold text-gray-800 font-mono" x-text="atletaSelecionado.pace && atletaSelecionado.pace !== '-' ? atletaSelecionado.pace + ' min/km' : 'N/D'"></span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center">
                        <span class="block text-xs font-bold uppercase text-gray-400">Velocidade</span>
                        <span class="text-sm font-bold text-gray-800 font-mono" x-text="atletaSelecionado.vel && atletaSelecionado.vel !== '-' ? atletaSelecionado.vel + ' km/h' : 'N/D'"></span>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-3 space-y-2 mb-6 border border-gray-100 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Modalidade:</span>
                        <span class="font-bold text-black" x-text="atletaSelecionado.p || '-'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Sexo / Ano:</span>
                        <span class="font-bold text-black" x-text="(atletaSelecionado.x || '-') + ' / ' + (atletaSelecionado.a || '-')"></span>
                    </div>
                </div>

                <button @click="modalAberto = false" class="w-full bg-black text-white font-bold py-3 rounded-xl hover:bg-gray-800 transition text-sm">
                    Fechar Detalhes
                </button>

            </div>
        </div>

        <!-- Menu Flutuante Celular -->
        <div class="fixed bottom-4 left-4 right-4 z-50 lg:hidden">
            <div class="bg-[#2a2a2c] rounded-[2rem] px-6 py-1.5 flex justify-between items-center shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5">
                <a href="{{ route('home') }}" class="w-12 h-12 flex items-center justify-center text-white/80 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                </a>
                <a href="{{ route('home') }}#search-section" class="w-12 h-12 flex items-center justify-center text-white/80 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </a>
                <a href="{{ route('resultados.index') }}" class="w-12 h-12 flex items-center justify-center bg-[#39ff14] rounded-full text-black hover:scale-105 transition-transform shadow-[0_0_15px_rgba(57,255,20,0.3)]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" /></svg>
                </a>
                <a href="{{ Auth::check() ? route('my.tickets') : route('login') }}" class="w-12 h-12 flex items-center justify-center text-white/80 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" /></svg>
                </a>
                <a href="{{ Auth::check() ? route('dashboard') : route('login') }}" class="w-12 h-12 flex items-center justify-center text-white/80 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                </a>
            </div>
        </div>

        <footer class="bg-black text-gray-400 py-6 mt-12 border-t border-gray-900 text-xs pb-28 lg:pb-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-gray-300">&copy; {{ date('Y') }} IngressosApp. Todos os direitos reservados.</p>
            </div>
        </footer>

    </body>
</html>