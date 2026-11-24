<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
        <title>Configurações da Conta - {{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="bg-gray-50 text-gray-900 font-sans antialiased flex flex-col min-h-screen">

        <!-- Navbar Customizada (Igual à do Meu Painel) -->
        <nav class="bg-black shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center">
                    <img src="{{ asset('images/1-Ruver Logo.svg') }}" alt="Ruver" class="h-6 w-auto">
                </a>

                <div class="flex items-center space-x-4">
                    <a href="{{ route('cliente.dashboard') }}" class="text-sm font-semibold text-gray-300 hover:text-white transition">← Início</a>
                </div>
            </div>
        </nav>

        <!-- Conteúdo Principal -->
        <main class="max-w-4xl mx-auto px-4 py-10 flex-grow w-full space-y-6">
            
            <!-- Cabeçalho da Página -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Configurações da Conta</h1>
                <p class="text-sm text-gray-500 mt-1">Atualize seus dados pessoais, senha e gerencie sua segurança.</p>
            </div>

            <!-- Box: Informações do Perfil (onde estão os campos que criamos) -->
            <div class="p-4 sm:p-8 bg-white shadow-sm sm:rounded-2xl border border-gray-200">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Box: Atualizar Senha -->
            <div class="p-4 sm:p-8 bg-white shadow-sm sm:rounded-2xl border border-gray-200">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Box: Excluir Conta -->
            <div class="p-4 sm:p-8 bg-white shadow-sm sm:rounded-2xl border border-gray-200">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
            
        </main>

        <!-- Footer -->
        <footer class="bg-black text-gray-400 py-6 mt-12 border-t border-gray-900 text-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-gray-300">&copy; {{ date('Y') }} IngressosApp. Todos os direitos reservados.</p>
            </div>
        </footer>

    </body>
</html>