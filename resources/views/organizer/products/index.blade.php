@extends('layouts.organizer')

@section('title', 'Meus Produtos')

@section('content')

    <!-- Cabeçalho da Página -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Meus Produtos</h2>
            <p class="text-sm text-gray-500 mt-1">Gerencie produtos adicionais para vender com seus ingressos.</p>
        </div>
        <a href="{{ route('organizer.products.create') }}" class="inline-flex items-center px-6 py-3 bg-black hover:bg-gray-800 text-white font-bold rounded-xl shadow-sm transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Novo Produto
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-2xl relative mb-6 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Grid de Produtos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        @forelse($products as $product)
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <!-- Imagem do Produto -->
                    <div class="aspect-[4/3] bg-gray-100 relative">
                        @if($product->foto)
                            <img src="{{ asset('storage/' . $product->foto) }}" alt="{{ $product->titulo }}" class="w-full h-full object-cover">
                        @else
                            <div class="flex items-center justify-center h-full text-gray-400">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        @endif
                        <div class="absolute top-3 right-3">
                            <span class="px-3 py-1 text-xs font-bold rounded-lg shadow-sm {{ $product->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $product->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                    </div>

                    <!-- Info do Produto -->
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ $product->categoria }} • {{ ucfirst($product->tipo) }}</span>
                                <h3 class="text-lg font-bold text-gray-900 leading-tight mt-1">{{ $product->titulo }}</h3>
                            </div>
                        </div>
                        
                        <p class="text-xl font-extrabold text-black mt-3">
                            R$ {{ number_format($product->preco_base, 2, ',', '.') }}
                        </p>

                        <!-- Variações / Atributos -->
                        @if($product->attributes->count() > 0)
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-xs font-semibold text-gray-500 mb-2">Variações e Estoque:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($product->attributes as $attr)
                                        <span class="inline-flex items-center px-2.5 py-1 bg-gray-50 border border-gray-200 rounded-md text-xs font-medium text-gray-700">
                                            {{ $attr->nome }} 
                                            <span class="ml-1 text-gray-400 font-normal">({{ $attr->estoque }} un)</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Rodapé do Card (Botões de Ação) -->
                <div class="p-5 pt-0 mt-auto">
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                        <a href="{{ route('organizer.products.edit', $product->id) }}" class="inline-flex items-center px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold rounded-xl transition">
                            Editar
                        </a>
                        
                        <!-- Botão de Excluir com Confirmação e Soft Delete -->
                        <form action="{{ route('organizer.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-3.5 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold rounded-xl transition">
                                Excluir
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <!-- Estado Vazio -->
            <div class="col-span-full bg-white rounded-3xl border border-dashed border-gray-300 p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <h3 class="text-lg font-bold text-gray-900">Nenhum produto cadastrado</h3>
                <p class="text-gray-500 mt-2 mb-6">Crie camisetas, copos, ou combos para vender junto com seus ingressos.</p>
                <a href="{{ route('organizer.products.create') }}" class="inline-flex items-center px-6 py-3 bg-black hover:bg-gray-800 text-white font-bold rounded-xl shadow-sm transition">
                    Criar Primeiro Produto
                </a>
            </div>
        @endforelse

    </div>

@endsection