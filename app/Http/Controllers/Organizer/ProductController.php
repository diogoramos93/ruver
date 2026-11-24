<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Organizer;
use App\Models\ProductAttribute; // <-- Garanta que essa model está sendo importada se ainda não estava
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $organizer = $user->organizer ?? 
                     Organizer::find($user->organizer_id) ?? 
                     Organizer::where('email', $user->email)->first();

        if (!$organizer) {
            return redirect()->route('organizer.settings.index')
                             ->with('error', 'Por favor, salve suas configurações antes de cadastrar produtos.');
        }

        $products = $organizer->products()->with('attributes')->latest()->get();

        return view('organizer.products.index', compact('products'));
    }

    public function create()
    {
        return view('organizer.products.create');
    }

    // 👇 A MÁGICA DE SALVAR ACONTECE AQUI 👇
    public function store(Request $request)
    {
        // 1. Valida se os dados obrigatórios vieram preenchidos
        $request->validate([
            'titulo' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'tipo' => 'required|in:fisico,digital',
            'preco_base' => 'required|numeric|min:0',
            'foto' => 'nullable|image|max:2048', // Aceita imagem até 2MB
            
            // Valida as variações/tamanhos dinâmicos
            'atributos' => 'required|array|min:1',
            'atributos.*.nome' => 'required|string|max:255',
            'atributos.*.preco_adicional' => 'required|numeric|min:0',
            'atributos.*.estoque' => 'nullable|integer|min:0', // <-- AJUSTE AQUI: Mudou de required para nullable
        ]);

        // 2. Acha o dono do produto
        $user = auth()->user();
        $organizer = $user->organizer ?? Organizer::where('email', $user->email)->first();

        // 3. Faz o upload da foto se ela existir
        $caminhoFoto = null;
        if ($request->hasFile('foto')) {
            // Salva na pasta 'produtos' dentro do disco público
            $caminhoFoto = $request->file('foto')->store('produtos', 'public');
        }

        // 4. Salva os dados gerais na tabela `products`
        $product = $organizer->products()->create([
            'titulo' => $request->titulo,
            'categoria' => $request->categoria,
            'tipo' => $request->tipo,
            'preco_base' => $request->preco_base,
            'foto' => $caminhoFoto,
            'ativo' => true,
        ]);

        // 5. Salva cada tamanho/variação na tabela `product_attributes`
        foreach ($request->atributos as $atributo) {
            ProductAttribute::create([
                'product_id' => $product->id, // <-- AJUSTE AQUI: Mudou de $produto->id para $product->id
                'nome' => $atributo['nome'],
                'preco_adicional' => $atributo['preco_adicional'],
                // Se o estoque vier vazio (null ou string vazia), salva como NULL no banco
                'estoque' => $atributo['estoque'] === '' || $atributo['estoque'] === null ? null : (int) $atributo['estoque'], 
            ]);
        }

        // 6. Redireciona de volta para a lista com mensagem de sucesso
        return redirect()->route('organizer.products.index')
                         ->with('success', 'Produto criado e variações salvas com sucesso!');
    }

    public function edit(Product $product)
    {
        $user = auth()->user();
        $organizer = $user->organizer ?? Organizer::where('email', $user->email)->first();

        // Segurança: Garante que o organizador só pode editar os próprios produtos
        if ($product->organizer_id !== $organizer->id) {
            abort(403, 'Acesso negado.');
        }

        // Carrega o produto junto com os tamanhos/variações
        $product->load('attributes');

        return view('organizer.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $user = auth()->user();
        $organizer = $user->organizer ?? Organizer::where('email', $user->email)->first();

        if ($product->organizer_id !== $organizer->id) {
            abort(403, 'Acesso negado.');
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'tipo' => 'required|in:fisico,digital',
            'preco_base' => 'required|numeric|min:0',
            'foto' => 'nullable|image|max:2048',
            'atributos' => 'required|array|min:1',
            'atributos.*.nome' => 'required|string|max:255',
            'atributos.*.preco_adicional' => 'required|numeric|min:0',
            'atributos.*.estoque' => 'nullable|integer|min:0', // <-- AJUSTE AQUI: Mudou de required para nullable
        ]);

        // Se mandou foto nova, atualiza
        if ($request->hasFile('foto')) {
            $product->foto = $request->file('foto')->store('produtos', 'public');
        }

        // Atualiza os dados básicos
        $product->update([
            'titulo' => $request->titulo,
            'categoria' => $request->categoria,
            'tipo' => $request->tipo,
            'preco_base' => $request->preco_base,
            'ativo' => $request->has('ativo'), // Pega do checkbox
        ]);

        // Atualiza as variações preservando os IDs existentes para não quebrar o vínculo com compras passadas
        $existingIds = $product->attributes()->pluck('id')->toArray();
        $keptIds = [];

        foreach ($request->atributos as $atributoData) {
            if (isset($atributoData['id']) && in_array($atributoData['id'], $existingIds)) {
                $product->attributes()->where('id', $atributoData['id'])->update([
                    'nome' => $atributoData['nome'],
                    'preco_adicional' => $atributoData['preco_adicional'],
                    // Salva como NULL se vier vazio
                    'estoque' => $atributoData['estoque'] === '' || $atributoData['estoque'] === null ? null : (int) $atributoData['estoque'], 
                ]);
                $keptIds[] = $atributoData['id'];
            } else {
                $newAttr = $product->attributes()->create([
                    'nome' => $atributoData['nome'],
                    'preco_adicional' => $atributoData['preco_adicional'],
                    // Salva como NULL se vier vazio
                    'estoque' => $atributoData['estoque'] === '' || $atributoData['estoque'] === null ? null : (int) $atributoData['estoque'], 
                ]);
                $keptIds[] = $newAttr->id;
            }
        }

        // Deleta apenas os atributos removidos pelo organizador no formulário
        $toDelete = array_diff($existingIds, $keptIds);
        if (!empty($toDelete)) {
            $product->attributes()->whereIn('id', $toDelete)->delete();
        }

        return redirect()->route('organizer.products.index')->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy(Product $product)
    {
        $user = auth()->user();
        $organizer = $user->organizer ?? Organizer::where('email', $user->email)->first();

        // Segurança: Garante que só o dono pode excluir
        if ($product->organizer_id !== $organizer->id) {
            abort(403, 'Acesso negado.');
        }

        // Realiza o Soft Delete (o produto some da lista, mas fica salvo no banco)
        $product->delete();

        return redirect()->route('organizer.products.index')
                         ->with('success', 'Produto removido com sucesso!');
    }
}