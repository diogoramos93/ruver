<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'IngressosApp'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased flex flex-col min-h-screen">

    <!-- MENU PRINCIPAL PADRÃO PARA TODO O PAINEL -->
    <nav class="bg-black shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <div class="flex items-center space-x-8">
                <a href="{{ route('organizer.events.index') }}" class="flex items-center">
                    <img src="{{ asset('images/1-Ruver Logo.svg') }}" alt="Ruver" class="h-6 w-auto">
                </a>
                <div class="hidden md:flex space-x-6">
    <a href="{{ route('organizer.events.index') }}" class="text-sm transition pb-1 {{ request()->routeIs('organizer.events.*') ? 'font-bold text-white border-b-2 border-white' : 'font-semibold text-gray-400 hover:text-white' }}">
        Eventos
    </a>
    
    <a href="{{ route('organizer.products.index') }}" class="text-sm transition pb-1 {{ request()->routeIs('organizer.products.*') ? 'font-bold text-white border-b-2 border-white' : 'font-semibold text-gray-400 hover:text-white' }}">
        Produtos
    </a>
    
    <a href="{{ route('organizer.settings.index') }}" class="text-sm transition pb-1 {{ request()->routeIs('organizer.settings.*') ? 'font-bold text-white border-b-2 border-white' : 'font-semibold text-gray-400 hover:text-white' }}">
        Configurações
    </a>

    <a href="{{ route('organizer.financial.global') }}" class="text-sm transition pb-1 {{ request()->routeIs('organizer.financial.*') ? 'font-bold text-white border-b-2 border-white' : 'font-semibold text-gray-400 hover:text-white' }}">
        Financeiro
    </a>
</div>
            </div>
            <div class="flex items-center space-x-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-gray-300 hover:text-white transition">Sair</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- O CONTEÚDO DAS OUTRAS PÁGINAS VAI SER INJETADO AQUI 👇 -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full">
        @yield('content')
    </main>

    <!-- FOOTER PADRÃO -->
    <footer class="bg-black text-gray-400 py-6 mt-12 border-t border-gray-900 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-gray-300">&copy; {{ date('Y') }} {{ config('app.name', 'IngressosApp') }}. Todos os direitos reservados.</p>
        </div>
    </footer>

</body>
</html>