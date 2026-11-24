<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastro - {{ config('app.name', 'Ruver') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased min-h-screen flex flex-col bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]">

    <div class="flex-grow flex flex-col items-center justify-center p-4 py-10">
        <!-- Card de Registro -->
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 w-full max-w-2xl p-8 sm:p-10 relative overflow-hidden">
            
            <div class="absolute top-0 left-0 w-full h-2 bg-black"></div>

            <!-- Header -->
            <div class="text-center mb-8 mt-2">
                <a href="{{ route('home') }}" class="inline-block mb-5">
                    <img src="{{ asset('images/1-Ruver Logo.svg') }}" alt="Ruver" class="h-10 w-auto mx-auto transition-transform hover:scale-105">
                </a>
                <h2 class="text-2xl font-extrabold text-gray-900">Crie sua conta</h2>
                <p class="text-sm text-gray-500 mt-1">Junte-se a nós e garanta sua vaga nos melhores eventos.</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Nome Completo -->
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-bold text-gray-700 mb-1.5">Nome Completo *</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" 
                               class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black px-4 py-3 text-sm transition"
                               placeholder="Digite seu nome completo">
                        @error('name') <p class="text-red-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <!-- E-mail -->
                    <div class="sm:col-span-2">
                        <label for="email" class="block text-sm font-bold text-gray-700 mb-1.5">E-mail *</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" 
                               class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black px-4 py-3 text-sm transition"
                               placeholder="exemplo@email.com">
                        @error('email') <p class="text-red-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <!-- CPF -->
                    <div>
                        <label for="cpf" class="block text-sm font-bold text-gray-700 mb-1.5">CPF</label>
                        <input id="cpf" type="text" name="cpf" value="{{ old('cpf') }}" 
                               class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black px-4 py-3 text-sm transition"
                               placeholder="000.000.000-00">
                        @error('cpf') <p class="text-red-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <!-- Passaporte -->
                    <div>
                        <label for="passaporte" class="block text-sm font-bold text-gray-700 mb-1.5">Passaporte</label>
                        <input id="passaporte" type="text" name="passaporte" value="{{ old('passaporte') }}" 
                               class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black px-4 py-3 text-sm transition"
                               placeholder="Para estrangeiros">
                        @error('passaporte') <p class="text-red-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="sm:col-span-2 -mt-3 mb-1">
                        <p class="text-xs text-gray-500 font-medium">* Preencha obrigatoriamente o CPF ou o Passaporte.</p>
                    </div>

                    <!-- Data de Nascimento -->
                    <div>
                        <label for="data_nascimento" class="block text-sm font-bold text-gray-700 mb-1.5">Data de Nascimento *</label>
                        <input id="data_nascimento" type="date" name="data_nascimento" value="{{ old('data_nascimento') }}" required
                               class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black px-4 py-3 text-sm transition text-gray-600">
                        @error('data_nascimento') <p class="text-red-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <!-- Telefone -->
                    <div>
                        <label for="telefone" class="block text-sm font-bold text-gray-700 mb-1.5">WhatsApp / Telefone</label>
                        <input id="telefone" type="text" name="telefone" value="{{ old('telefone') }}" 
                               class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black px-4 py-3 text-sm transition"
                               placeholder="(00) 00000-0000">
                        @error('telefone') <p class="text-red-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <!-- Senha -->
                    <div>
                        <label for="password" class="block text-sm font-bold text-gray-700 mb-1.5">Senha *</label>
                        <input id="password" type="password" name="password" required autocomplete="new-password" 
                               class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black px-4 py-3 text-sm transition"
                               placeholder="••••••••">
                        @error('password') <p class="text-red-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <!-- Confirmar Senha -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-1.5">Confirmar Senha *</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" 
                               class="w-full rounded-xl border-gray-300 shadow-sm focus:border-black focus:ring-black px-4 py-3 text-sm transition"
                               placeholder="••••••••">
                        @error('password_confirmation') <p class="text-red-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Botão de Cadastro -->
                <button type="submit" class="w-full bg-black hover:bg-gray-800 text-white font-bold py-4 px-4 rounded-xl shadow-md transition-all flex justify-center items-center mt-6 group">
                    Criar Minha Conta
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </form>

            <!-- Link de Login -->
            <div class="mt-8 text-center text-sm text-gray-600 border-t border-gray-100 pt-6">
                Já possui uma conta? 
                <a href="{{ route('login') }}" class="font-extrabold text-black hover:underline transition">Faça login aqui</a>
            </div>

        </div>
        
        <!-- Rodapé -->
        <div class="mt-8 w-full text-center text-xs text-gray-400 font-medium">
            &copy; {{ date('Y') }} {{ config('app.name', 'Ruver') }}. Todos os direitos reservados.
        </div>
    </div>

</body>
</html>