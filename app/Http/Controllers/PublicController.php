<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    // Página inicial listando os eventos ativos/cadastrados (inalterada)
    public function index(Request $request)
    {
        $organizer = $request->get('currentOrganizer');

        $query = Event::with('tickets')->where('status', 'ativo');

        if ($organizer) {
            $query->where('organizer_id', $organizer->id);
        }

        if ($request->filled('search')) {
            $busca = $request->search;
            $query->where(function ($q) use ($busca) {
                $q->where('titulo', 'like', "%{$busca}%")
                  ->orWhere('cidade', 'like', "%{$busca}%");
            });
        }

        $events = $query
            ->orderBy('data_inicio', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString();

        $favoritosIds = [];
        if (auth()->check()) {
            $favoritosIds = auth()->user()->favoritos()->pluck('events.id')->toArray();
        }

        return view('welcome', compact('events', 'favoritosIds', 'organizer'));
    }

    // Página de detalhes de um evento específico (inalterada)
    public function show(Request $request, $slug)
    {
        $organizer = $request->get('currentOrganizer');

        $query = Event::with(['tickets', 'organizer'])
            ->where('slug', $slug)
            ->where('status', 'ativo');

        if ($organizer) {
            $query->where('organizer_id', $organizer->id);
        }

        $event = $query->firstOrFail();

        return view('event-show', compact('event', 'organizer'));
    }

    /**
     * Localiza o arquivo de resultados dentro da pasta já usada pela Ruver.
     * Mantém prioridade para .clac/.clax, preservando as corridas antigas.
     * No formato RaceTag, aceita APENAS 'results.json' (não event.json).
     */
    private function localizarArquivoResultados(string $pasta): ?string
    {
        $disk = Storage::disk('public');

        if (!$disk->exists($pasta)) {
            return null;
        }

        $arquivos = $disk->files($pasta);
        sort($arquivos, SORT_STRING);

        foreach ($arquivos as $arquivo) {
            $ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
            if ($ext === 'clac' || $ext === 'clax') {
                return $arquivo;
            }
        }

        foreach ($arquivos as $arquivo) {
            if (strtolower(basename($arquivo)) === 'results.json') {
                return $arquivo;
            }
        }

        return null;
    }

    // Lista eventos que tenham resultados Weclex OU RaceTag.
    public function listaResultados(Request $request)
    {
        $organizer = $request->get('currentOrganizer');

        $query = Event::where('status', 'ativo');
        if ($organizer) {
            $query->where('organizer_id', $organizer->id);
        }

        $eventosAtivos = $query->orderBy('data_inicio', 'desc')->get();
        $events = collect();

        foreach ($eventosAtivos as $event) {
            $pasta = 'resultados_weclex/' . $event->slug;

            if ($this->localizarArquivoResultados($pasta) !== null) {
                $events->push($event);
            }
        }

        return view('resultados-index', compact('events', 'organizer'));
    }

    /**
     * Normaliza o results.json da RaceTag para o formato de $atletas
     * já esperado pela view event-resultados.blade.php.
     *
     * Atenção: 'r' e 'c' são códigos, não nomes legíveis.
     * 'rg' é a posição oficial dentro da prova/sexo neste JSON;
     * 'rn' é a posição oficial da categoria quando fornecida.
     */
    private function normalizarAtletasRaceTag(array $dados): array
    {
        $atletas = [];

        foreach ($dados as $item) {
            if (!is_array($item)) {
                continue;
            }

            $numero = isset($item['n']) ? trim((string) $item['n']) : '';
            $nome = trim((string) ($item['nm'] ?? ''));

            if ($numero === '' || $nome === '') {
                continue;
            }

            $codigoProva = trim((string) ($item['r'] ?? ''));
            $codigoCategoria = trim((string) ($item['c'] ?? ''));
            $sexo = strtoupper(trim((string) ($item['g'] ?? '')));
            $nascimento = $item['b'] ?? null;
            $ano = is_array($nascimento) && isset($nascimento[0])
                ? (string) $nascimento[0]
                : '';

            $posGeral = isset($item['rg']) && is_numeric($item['rg']) && (int) $item['rg'] > 0
                ? (int) $item['rg']
                : null;
            $posCategoria = isset($item['rn']) && is_numeric($item['rn']) && (int) $item['rn'] > 0
                ? (int) $item['rn']
                : null;

            $tempoLiquido = trim((string) ($item['tn'] ?? ''));
            $tempoBruto = trim((string) ($item['tg'] ?? ''));

            $atletas[] = [
                // Campos de compatibilidade com a view atual.
                'd' => $numero,
                'n' => $nome,
                'x' => $sexo,
                'a' => $ano, // A view atual mostra "Ano", e não a idade.
                'p' => $codigoProva !== '' ? 'Prova ' . $codigoProva : 'Prova não informada',
                'cat' => 'Categoria ' . ($codigoCategoria !== '' ? $codigoCategoria : 'N/D')
                    . ' / Prova ' . ($codigoProva !== '' ? $codigoProva : 'N/D')
                    . ' / ' . ($sexo !== '' ? $sexo : 'N/D'),
                'liquido' => $tempoLiquido !== '' ? $tempoLiquido : '-',
                'bruto' => $tempoBruto !== '' ? $tempoBruto : '-',
                // Não inventar pace/velocidade sem a distância oficial da prova.
                'pace' => '-',
                'vel' => '-',

                // Campos extras para usar as classificações oficiais da RaceTag.
                'prova_codigo' => $codigoProva,
                'categoria_codigo' => $codigoCategoria,
                'pos_geral' => $posGeral,
                'pos_categoria' => $posCategoria,
            ];
        }

        // A ordem do JSON não é a classificação. Organiza por prova, sexo
        // e posição oficial, deixando quem não tem resultado por último.
        usort($atletas, function (array $a, array $b): int {
            $cmp = strcmp($a['prova_codigo'], $b['prova_codigo']);
            if ($cmp !== 0) {
                return $cmp;
            }

            $cmp = strcmp($a['x'], $b['x']);
            if ($cmp !== 0) {
                return $cmp;
            }

            $cmp = ($a['pos_geral'] ?? PHP_INT_MAX) <=> ($b['pos_geral'] ?? PHP_INT_MAX);
            return $cmp !== 0 ? $cmp : strcmp($a['n'], $b['n']);
        });

        // O total da classificação geral é por prova + sexo,
        // considerando apenas quem tem posição oficial.
        $totais = [];
        foreach ($atletas as $atleta) {
            if ($atleta['pos_geral'] !== null) {
                $chave = $atleta['prova_codigo'] . '|' . $atleta['x'];
                $totais[$chave] = ($totais[$chave] ?? 0) + 1;
            }
        }

        foreach ($atletas as &$atleta) {
            $chave = $atleta['prova_codigo'] . '|' . $atleta['x'];
            $atleta['total_geral'] = $totais[$chave] ?? 0;
        }
        unset($atleta);

        return $atletas;
    }

    // Exibe resultados Weclex (.clac/.clax) OU RaceTag (results.json).
    public function resultados(Request $request, $slug)
    {
        $organizer = $request->get('currentOrganizer');

        $query = Event::where('slug', $slug);
        if ($organizer) {
            $query->where('organizer_id', $organizer->id);
        }

        $event = $query->firstOrFail();

        $pasta = 'resultados_weclex/' . $event->slug;
        $arquivoEncontrado = $this->localizarArquivoResultados($pasta);
        $conteudoWeclex = null;
        $atletas = [];
        $tipoImportacao = 'weclex';

        if ($arquivoEncontrado !== null) {
            $ext = strtolower(pathinfo($arquivoEncontrado, PATHINFO_EXTENSION));
            $conteudoBruto = Storage::disk('public')->get($arquivoEncontrado);

            if ($ext === 'clac' || $ext === 'clax') {
                // LÓGICA WECLEX ORIGINAL — preservada.
                $conteudoWeclex = @simplexml_load_string($conteudoBruto);

                if ($conteudoWeclex) {
                    $mapaResultados = [];
                    $xpathRes = $conteudoWeclex->xpath('//Resultats/R');
                    if ($xpathRes) {
                        foreach ($xpathRes as $res) {
                            $attrRes = $res->attributes();
                            $idAtleta = (string) ($attrRes['d'] ?? '');
                            if ($idAtleta !== '') {
                                $mapaResultados[$idAtleta] = [
                                    't'  => (string) ($attrRes['t'] ?? '00:00:00'),
                                    're' => (string) ($attrRes['re'] ?? '00:00:00'),
                                ];
                            }
                        }
                    }

                    $resultadosXpath = $conteudoWeclex->xpath('//E');
                    if ($resultadosXpath) {
                        foreach ($resultadosXpath as $atletaNode) {
                            $attr = $atletaNode->attributes();
                            $idD = (string) ($attr['d'] ?? '');
                            $nome = (string) ($attr['n'] ?? '');

                            if (!empty($nome) && $idD !== '') {
                                $tempoLiquido = $mapaResultados[$idD]['t'] ?? '00:00:00';
                                $tempoBruto = $mapaResultados[$idD]['re'] ?? '00:00:00';

                                $liqFormatado = str_replace(['h', "'"], [':', ':'], rtrim($tempoLiquido, ',0123456789'));
                                $brutoFormatado = str_replace(['h', "'"], [':', ':'], rtrim($tempoBruto, ',0123456789'));

                                $atletas[] = [
                                    'd' => $idD,
                                    'n' => $nome,
                                    'x' => (string) ($attr['x'] ?? ''),
                                    'a' => (string) ($attr['a'] ?? ''),
                                    'p' => (string) ($attr['p'] ?? 'Geral'),
                                    'cat' => (string) ($attr['ca'] ?? 'Geral'),
                                    'liquido' => $liqFormatado !== '' ? $liqFormatado : '00:00:00',
                                    'bruto' => $brutoFormatado !== '' ? $brutoFormatado : '00:00:00',
                                    'pace' => '06:30',
                                    'vel' => '9.2',
                                ];
                            }
                        }
                    }
                }
            } elseif (strtolower(basename($arquivoEncontrado)) === 'results.json') {
                // NOVO LEITOR RACETAG.
                $tipoImportacao = 'racetag';
                $dados = json_decode($conteudoBruto, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($dados) && array_values($dados) === $dados) {
                    $atletas = $this->normalizarAtletasRaceTag($dados);

                    // A view atual só apresenta os resultados quando
                    // $conteudoWeclex está preenchido. Mantém compatibilidade.
                    $conteudoWeclex = [
                        'nom' => $event->titulo,
                        'organisateur' => 'N/D',
                        'dates' => $event->data_inicio
                            ? \Carbon\Carbon::parse($event->data_inicio)->format('d/m/Y')
                            : 'N/D',
                    ];
                }
            }
        }

        return view('event-resultados', compact(
            'event',
            'conteudoWeclex',
            'atletas',
            'arquivoEncontrado',
            'organizer',
            'tipoImportacao'
        ));
    }
}
