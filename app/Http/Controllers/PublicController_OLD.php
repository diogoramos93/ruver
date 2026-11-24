<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    // Página inicial listando os eventos ativos/cadastrados
    public function index(Request $request)
    {
        // 1. Pega o organizador injetado pelo middleware de White-Label (se houver)
        $organizer = $request->get('currentOrganizer');

        // 2. Inicia a busca trazendo os ingressos e APENAS eventos com status 'ativo'
        $query = Event::with('tickets')->where('status', 'ativo');

        // 3. Se estiver acessando via domínio de um organizador, filtra estritamente pelos eventos dele
        if ($organizer) {
            $query->where('organizer_id', $organizer->id);
        }

        // 4. Se o cliente digitou algo na barra de busca da tela inicial
        if ($request->filled('search')) {
            $busca = $request->search;
            $query->where(function($q) use ($busca) {
                $q->where('titulo', 'like', "%{$busca}%")
                  ->orWhere('cidade', 'like', "%{$busca}%");
            });
        }

        // 5. Traz os eventos ordenados pela data que vai acontecer primeiro
        $events = $query
    ->orderBy('data_inicio', 'asc')
    ->orderBy('id', 'asc')
    ->paginate(10)
    ->withQueryString();

        // Busca os IDs dos eventos favoritados pelo usuário logado
        $favoritosIds = [];
        if (auth()->check()) {
            $favoritosIds = auth()->user()->favoritos()->pluck('events.id')->toArray();
        }

        // 6. Retorna a view enviando os eventos, favoritos E o organizador (para logomarca e cores dinâmicas)
        return view('welcome', compact('events', 'favoritosIds', 'organizer'));
    }

    // Página de detalhes de um evento específico
    public function show(Request $request, $slug)
    {
        $organizer = $request->get('currentOrganizer');

        $query = Event::with(['tickets', 'organizer']) 
                      ->where('slug', $slug)
                      ->where('status', 'ativo');

        // Se estiver num subdomínio de organizador, garante que o evento pertence a ele
        if ($organizer) {
            $query->where('organizer_id', $organizer->id);
        }

        $event = $query->firstOrFail();

        return view('event-show', compact('event', 'organizer'));
    }

    // 👇 Página que lista apenas os eventos que possuem arquivo de resultados (.clac ou .clax)
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
            $pastaWeclex = 'resultados_weclex/' . $event->slug;
            
            if (Storage::disk('public')->exists($pastaWeclex)) {
                $arquivos = Storage::disk('public')->files($pastaWeclex);
                
                $temArquivoValido = false;
                foreach ($arquivos as $arquivo) {
                    $ext = pathinfo($arquivo, PATHINFO_EXTENSION);
                    if ($ext === 'clac' || $ext === 'clax') {
                        $temArquivoValido = true;
                        break;
                    }
                }

                if ($temArquivoValido) {
                    $events->push($event);
                }
            }
        }

        return view('resultados-index', compact('events', 'organizer'));
    }

    // 👇 Função que exibe os resultados do Weclex
    public function resultados(Request $request, $slug)
    {
        $organizer = $request->get('currentOrganizer');

        $query = Event::where('slug', $slug);
        if ($organizer) {
            $query->where('organizer_id', $organizer->id);
        }

        $event = $query->firstOrFail();
        
        $pastaWeclex = 'resultados_weclex/' . $event->slug;
        $arquivos = Storage::disk('public')->files($pastaWeclex);

        $conteudoWeclex = null;
        $atletas = [];
        $arquivoEncontrado = null;

        foreach ($arquivos as $arquivo) {
            $ext = pathinfo($arquivo, PATHINFO_EXTENSION);
            if ($ext === 'clac' || $ext === 'clax') {
                $arquivoEncontrado = $arquivo;
                $conteudoBruto = Storage::disk('public')->get($arquivoEncontrado);
                
                $conteudoWeclex = @simplexml_load_string($conteudoBruto);
                
                if ($conteudoWeclex) {
                    $mapaResultados = [];
                    $xpathRes = $conteudoWeclex->xpath('//Resultats/R');
                    if ($xpathRes) {
                        foreach ($xpathRes as $res) {
                            $attrRes = $res->attributes();
                            $idAtleta = (string)($attrRes['d'] ?? '');
                            if ($idAtleta !== '') {
                                $mapaResultados[$idAtleta] = [
                                    't'  => (string)($attrRes['t'] ?? '00:00:00'),
                                    're' => (string)($attrRes['re'] ?? '00:00:00')
                                ];
                            }
                        }
                    }

                    $resultadosXpath = $conteudoWeclex->xpath('//E');
                    if ($resultadosXpath) {
                        foreach ($resultadosXpath as $atletaNode) {
                            $attr = $atletaNode->attributes();
                            $idD = (string)($attr['d'] ?? '');
                            $nome = (string)($attr['n'] ?? '');

                            if (!empty($nome) && $idD !== '') {
                                $tempoLiquido = $mapaResultados[$idD]['t'] ?? '00:00:00';
                                $tempoBruto = $mapaResultados[$idD]['re'] ?? '00:00:00';

                                $liqFormatado = str_replace(['h', "'"], [':', ':'], rtrim($tempoLiquido, ',0123456789'));
                                $brutoFormatado = str_replace(['h', "'"], [':', ':'], rtrim($tempoBruto, ',0123456789'));

                                $atletas[] = [
                                    'd' => $idD,
                                    'n' => $nome,
                                    'x' => (string)($attr['x'] ?? ''),
                                    'a' => (string)($attr['a'] ?? ''),
                                    'p' => (string)($attr['p'] ?? 'Geral'),
                                    'cat' => (string)($attr['ca'] ?? 'Geral'),
                                    'liquido' => $liqFormatado !== '' ? $liqFormatado : '00:00:00',
                                    'bruto' => $brutoFormatado !== '' ? $brutoFormatado : '00:00:00',
                                    'pace' => '06:30',
                                    'vel' => '9.2'
                                ];
                            }
                        }
                    }
                }
                break; 
            }
        }

        return view('event-resultados', compact('event', 'conteudoWeclex', 'atletas', 'arquivoEncontrado', 'organizer'));
    }
}