<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\OrderParticipant;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $organizer = auth()->user()->organizer ?? \App\Models\Organizer::where('user_id', auth()->id())->orWhere('email', auth()->user()->email)->first();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            abort(403, 'Ação não autorizada.');
        }

        $ticketIds = $event->tickets()->pluck('id');

        // Garante busca resiliente: busca pelos ingressos atuais E por pedidos vinculados ao evento
        $query = OrderParticipant::where(function($q) use ($ticketIds, $event) {
            $q->whereIn('ticket_id', $ticketIds)
              ->orWhereHas('order.ticket', function($orderQuery) use ($event) {
                  $orderQuery->where('event_id', $event->id);
              });
        })->with(['ticket', 'order.user', 'formAnswers']);

        if ($request->filled('search')) {
            $busca = $request->search;
            $query->where(function($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('cpf', 'like', "%{$busca}%")
                  ->orWhere('equipe', 'like', "%{$busca}%")
                  ->orWhere('cidade', 'like', "%{$busca}%");
            });
        }

        // Filtro para listar apenas participantes que enviaram comprovante/imagem
        if ($request->filled('com_anexo') && $request->com_anexo == '1') {
            $query->whereHas('formAnswers', function($q) {
                $q->where('tipo', 'image')
                  ->orWhere('resposta', 'like', 'respostas_formularios/%');
            });
        }

        // Coleta os IDs de atributos para traduzir os produtos selecionados
        $allParticipantes = (clone $query)->latest()->get();
        $attributeIds = [];
        foreach ($allParticipantes as $p) {
            if (is_array($p->produtos)) {
                foreach ($p->produtos as $key => $val) {
                    if (is_numeric($val)) {
                        $attributeIds[] = (int) $val;
                    }
                    if (is_numeric($key)) {
                        $attributeIds[] = (int) $key;
                    }
                }
            }
        }

        $attributesMap = [];
        if (!empty($attributeIds)) {
            $attrs = \App\Models\ProductAttribute::with(['product' => function($q) {
                $q->withTrashed();
            }])->whereIn('id', array_unique($attributeIds))->get();

            foreach ($attrs as $attr) {
                $productName = $attr->product ? $attr->product->titulo : 'Produto';
                $attributesMap[$attr->id] = "{$productName}: {$attr->nome}";
            }
        }

        // Função de formatação amigável de produtos
        $formatProdutos = function($produtosArr) use ($attributesMap) {
            if (!is_array($produtosArr) || empty($produtosArr)) {
                return is_string($produtosArr) ? $produtosArr : '-';
            }
            $formatted = [];
            foreach ($produtosArr as $key => $val) {
                if (is_numeric($val) && isset($attributesMap[$val])) {
                    $formatted[] = $attributesMap[$val];
                } elseif (is_numeric($key) && isset($attributesMap[$key])) {
                    $formatted[] = $attributesMap[$key];
                } else {
                    $formatted[] = is_numeric($key) ? $val : "$key: $val";
                }
            }
            return implode(', ', $formatted);
        };

        // Exportação CRUA e PADRÃO para Excel (sem estilo, sem cor)
        if ($request->has('export')) {
            $filename = "participantes-evento-{$event->id}-" . date('Y-m-d') . ".xls";

            // Identifica perguntas que realmente possuem respostas neste evento.
            // Chave: ID do formulário + ID da pergunta, evitando misturar perguntas homônimas.
            // Se a pergunta foi excluída, os IDs e textos preservados em form_answers continuam válidos.
            $colunasFormularios = [];
            foreach ($allParticipantes as $p) {
                foreach ($p->formAnswers as $answer) {
                    $chave = ($answer->form_id ?? 'excluido') . ':' . ($answer->form_field_id ?? 'excluido:' . $answer->id);
                    if (!isset($colunasFormularios[$chave])) {
                        $colunasFormularios[$chave] = [
                            'form_id' => $answer->form_id,
                            'titulo' => $answer->pergunta ?: 'Pergunta personalizada',
                        ];
                    }
                }
            }

            // Se duas perguntas tiverem o mesmo título, acrescenta o identificador do formulário.
            $titulosContagem = [];
            foreach ($colunasFormularios as $coluna) {
                $titulosContagem[$coluna['titulo']] = ($titulosContagem[$coluna['titulo']] ?? 0) + 1;
            }

            $headers = [
                "Content-Type" => "application/vnd.ms-excel; charset=UTF-8",
                "Content-Disposition" => "attachment; filename=\"{$filename}\"",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];

            $callback = function() use ($allParticipantes, $formatProdutos, $colunasFormularios, $titulosContagem) {
                $esc = static function ($value) {
                    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                };

                $formatResposta = static function ($resposta) {
                    if ($resposta === null || $resposta === '') {
                        return '-';
                    }
                    if (is_string($resposta) && str_starts_with($resposta, 'respostas_formularios/')) {
                        return url('storage/' . $resposta);
                    }
                    if (is_array($resposta)) {
                        $partes = [];
                        foreach ($resposta as $key => $valor) {
                            if (is_array($valor)) {
                                $valor = implode(', ', array_map('strval', $valor));
                            } elseif (is_bool($valor)) {
                                $valor = $valor ? 'Sim' : 'Não';
                            }
                            $partes[] = is_int($key) ? (string) $valor : "{$key}: {$valor}";
                        }
                        return implode(', ', $partes) ?: '-';
                    }
                    if (is_bool($resposta)) {
                        return $resposta ? 'Sim' : 'Não';
                    }
                    return (string) $resposta;
                };

                echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
                echo "<table>\n";
                echo "<thead>\n";
                echo "<tr>\n";
                echo "<th>ID</th>\n";
                echo "<th>Nome do Participante</th>\n";
                echo "<th>Documento (CPF / Passaporte)</th>\n";
                echo "<th>Data Nascimento</th>\n";
                echo "<th>Sexo</th>\n";
                echo "<th>Cidade</th>\n";
                echo "<th>Equipe</th>\n";
                echo "<th>Categoria / Modalidade</th>\n";
                echo "<th>Ingresso / Lote</th>\n";
                echo "<th>Produtos / Kit</th>\n";
                echo "<th>Status do Pedido</th>\n";
                echo "<th>Data da Compra</th>\n";

                foreach ($colunasFormularios as $chave => $coluna) {
                    $titulo = $coluna['titulo'];
                    if ($titulosContagem[$titulo] > 1) {
                        $titulo .= ' (Formulário ' . ($coluna['form_id'] ?? 'excluído') . ', campo ' . $chave . ')';
                    }
                    echo '<th>' . $esc($titulo) . "</th>\n";
                }

                echo "</tr>\n";
                echo "</thead>\n";
                echo "<tbody>\n";

                foreach ($allParticipantes as $p) {
                    $doc = $p->cpf ?: ($p->passaporte ?? '-');

                    echo "<tr>\n";
                    echo "<td>{$p->id}</td>\n";
                    echo "<td>" . $esc($p->nome ?? '') . "</td>\n";
                    // Exibe documento como texto, preservando zeros iniciais e evitando fórmula arbitrária.
                    echo '<td style="mso-number-format:\'\\@\'">' . $esc($doc) . "</td>\n";
                    echo "<td>" . ($p->data_nascimento ? \Carbon\Carbon::parse($p->data_nascimento)->format('d/m/Y') : '-') . "</td>\n";
                    echo "<td>" . $esc($p->sexo ?? '-') . "</td>\n";
                    echo "<td>" . $esc($p->cidade ?? '-') . "</td>\n";
                    echo "<td>" . $esc($p->equipe ?: '-') . "</td>\n";
                    echo "<td>" . $esc($p->ticket ? ($p->ticket->modalidade ?? '-') : '-') . "</td>\n";
                    echo "<td>" . $esc($p->ticket ? $p->ticket->nome : '-') . "</td>\n";
                    echo "<td>" . $esc($formatProdutos($p->produtos)) . "</td>\n";
                    echo "<td>" . $esc($p->order ? ucfirst($p->order->status) : '-') . "</td>\n";
                    echo "<td>" . ($p->created_at ? $p->created_at->format('d/m/Y H:i') : '-') . "</td>\n";

                    $respostasParticipante = [];
                    foreach ($p->formAnswers as $answer) {
                        $chave = ($answer->form_id ?? 'excluido') . ':' . ($answer->form_field_id ?? 'excluido:' . $answer->id);
                        $respostasParticipante[$chave][] = $formatResposta($answer->resposta);
                    }
                    foreach ($colunasFormularios as $chave => $coluna) {
                        $resposta = isset($respostasParticipante[$chave])
                            ? implode(' | ', $respostasParticipante[$chave])
                            : '';
                        echo '<td>' . $esc($resposta) . "</td>\n";
                    }

                    echo "</tr>\n";
                }

                echo "</tbody>\n";
                echo "</table>\n";
            };

            return response()->stream($callback, 200, $headers);
        }

        $participantes = $query->latest()->paginate(20)->withQueryString();

        // Produtos cadastrados do organizador para o modal de seleção
        $produtosDisponiveis = \App\Models\Product::where('organizer_id', $organizer->id)->with('attributes')->get();

        return view('organizer.participants.index', compact('event', 'participantes', 'attributesMap', 'produtosDisponiveis'));
    }

    // Vincular, alterar ou remover produto/kit do participante
    public function updateProducts(Request $request, Event $event, OrderParticipant $participant)
    {
        $organizer = auth()->user()->organizer ?? \App\Models\Organizer::where('user_id', auth()->id())->orWhere('email', auth()->user()->email)->first();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            abort(403, 'Ação não autorizada.');
        }

        // Se solicitou a remoção de todos os produtos
        if ($request->has('remover_produto') && $request->remover_produto == '1') {
            $participant->update(['produtos' => null]);
            return redirect()->back()->with('success', "Produtos desvinculados do participante '{$participant->nome}' com sucesso!");
        }

        // Suporta seleção de múltiplos produtos (array 'produtos') ou legado ('product_attribute_id')
        $produtosInput = $request->input('produtos', []);

        if (empty($produtosInput) && $request->filled('product_attribute_id')) {
            $attr = \App\Models\ProductAttribute::find($request->product_attribute_id);
            if ($attr) {
                $produtosInput = [$attr->product_id => $attr->id];
            }
        }

        $produtosFormatados = [];
        if (is_array($produtosInput)) {
            foreach ($produtosInput as $prodId => $attrId) {
                if (!empty($attrId)) {
                    $attr = \App\Models\ProductAttribute::where('id', $attrId)
                        ->where('product_id', $prodId)
                        ->first();
                    if ($attr) {
                        $produtosFormatados[(int)$prodId] = (int)$attr->id;
                    }
                }
            }
        }

        $participant->update([
            'produtos' => !empty($produtosFormatados) ? $produtosFormatados : null,
        ]);

        $total = count($produtosFormatados);
        $mensagem = $total > 0
            ? "{$total} produto(s)/kit(s) salvo(s) para o participante '{$participant->nome}' com sucesso!"
            : "Nenhum produto vinculado ao participante '{$participant->nome}'.";

        return redirect()->back()->with('success', $mensagem);
    }
}
