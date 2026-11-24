<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name', 'IngressosApp') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased min-h-screen flex flex-col bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]">

    <div class="flex-grow flex items-center justify-center p-4">
        <!-- Card de Login -->
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 w-full max-w-md p-8 sm:p-10 relative overflow-hidden">
            
            <!-- Detalhe de cor no topo do card -->
            <div class="absolute top-0 left-0 w-full h-2 bg-black"></div>

            <!-- Header -->
            <div class="text-center mb-8 mt-2">
                <a href="{{ route('home') }}" class="inline-block mb-5">
                    <img src="{{ asset('images/1-Ruver Logo.svg') }}" alt="Ruver" class="h-10 w-auto mx-auto transition-transform hover:scale-105">
                </a>
                <h2 class="text-2xl font-extrabold text-gray-900">Acesse sua conta</h2>
                <p class="text-sm text-gray-500 mt-1">Bem-vindo de volta! Sinta a emoção do esporte.</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-5 font-bold text-sm text-green-600 bg-green-50 p-3 rounded-xl border border-green-200 text-center">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Campo E-mail -->
                <div>
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-1.5">E-mail</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" 
                           class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black px-4 py-3 text-sm transition"
                           placeholder="Digite seu e-mail">
                    @error('email')
                        <p class="text-red-500 text-xs font-bold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Campo Senha -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-bold text-gray-700">Senha</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-bold text-gray-400 hover:text-black transition">
                                Esqueceu a senha?
                            </a>
                        @endif
                    </div>
                    <input id="password" type="password" name="password" required autocomplete="current-password" 
                           class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black px-4 py-3 text-sm transition"
                           placeholder="••••••••">
                    @error('password')
                        <p class="text-red-500 text-xs font-bold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Lembre-me -->
                <div class="flex items-center mt-2">
                    <input id="remember_me" type="checkbox" name="remember" 
                           class="rounded border-gray-300 text-black shadow-sm focus:ring-black w-4 h-4 cursor-pointer">
                    <label for="remember_me" class="ml-2 text-sm text-gray-600 font-medium cursor-pointer">Lembrar de mim na próxima vez</label>
                </div>

                <!-- Botão de Acesso -->
                <button type="submit" class="w-full bg-black hover:bg-gray-800 text-white font-bold py-3.5 px-4 rounded-xl shadow-md transition-all flex justify-center items-center mt-2 group">
                    Entrar na Plataforma
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </form>

            <!-- Link de Cadastro -->
            @if (Route::has('register'))
                <div class="mt-8 text-center text-sm text-gray-600 border-t border-gray-100 pt-6">
                    Ainda não tem conta? 
                    <a href="{{ route('register') }}" class="font-extrabold text-black hover:underline transition">Cadastre-se agora</a>
                </div>
            @endif

        </div>
        
        <!-- Rodapé simples na tela de login -->
        <div class="absolute bottom-4 w-full text-center text-xs text-gray-400 font-medium">
            &copy; {{ date('Y') }} {{ config('app.name', 'IngressosApp') }}. Todos os direitos reservados.
        </div>
    </div>

</body>
</html>