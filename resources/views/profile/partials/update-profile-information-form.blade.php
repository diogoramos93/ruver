<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <!-- Formulário principal com enctype para aceitar imagens -->
    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- FOTO -->
        <div>
            <x-input-label for="foto" value="Foto de Perfil" />
            @if(auth()->user()->foto)
                <img src="{{ asset('storage/' . auth()->user()->foto) }}" alt="Foto" class="w-20 h-20 rounded-full object-cover mb-3">
            @endif
            <input id="foto" name="foto" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-900 file:text-white hover:file:bg-gray-700" accept="image/*" />
            <x-input-error class="mt-2" :messages="$errors->get('foto')" />
        </div>

        <!-- NOME -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <!-- USUÁRIO -->
        <div>
            <x-input-label for="usuario" value="Usuário *" />
            <x-text-input id="usuario" name="usuario" type="text" class="mt-1 block w-full" :value="old('usuario', $user->usuario)" required />
            <x-input-error class="mt-2" :messages="$errors->get('usuario')" />
        </div>

        <!-- EMAIL -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- TELEFONE -->
        <div>
            <x-input-label for="telefone" value="Telefone" />
            <x-text-input id="telefone" name="telefone" type="text" class="mt-1 block w-full" :value="old('telefone', $user->telefone)" />
            <x-input-error class="mt-2" :messages="$errors->get('telefone')" />
        </div>

        <!-- DATA DE NASCIMENTO E SEXO 👇 -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="data_nascimento" value="Data de Nascimento *" />
                <x-text-input id="data_nascimento" name="data_nascimento" type="date" class="mt-1 block w-full" :value="old('data_nascimento', $user->data_nascimento ? \Carbon\Carbon::parse($user->data_nascimento)->format('Y-m-d') : '')" required />
                <x-input-error class="mt-2" :messages="$errors->get('data_nascimento')" />
            </div>
            <div>
                <x-input-label for="sexo" value="Sexo *" />
                <select id="sexo" name="sexo" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                    <option value="" disabled {{ old('sexo', $user->sexo) ? '' : 'selected' }}>Selecione...</option>
                    <option value="Masculino" {{ old('sexo', $user->sexo) === 'Masculino' ? 'selected' : '' }}>Masculino</option>
                    <option value="Feminino" {{ old('sexo', $user->sexo) === 'Feminino' ? 'selected' : '' }}>Feminino</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('sexo')" />
            </div>
        </div>

        <!-- CPF -->
        <div>
            <x-input-label for="cpf" value="CPF (Para residentes no Brasil)" />
            <x-text-input id="cpf" name="cpf" type="text" class="mt-1 block w-full" :value="old('cpf', $user->cpf)" placeholder="000.000.000-00" />
            <x-input-error class="mt-2" :messages="$errors->get('cpf')" />
        </div>

        <!-- PASSAPORTE -->
        <div>
            <x-input-label for="passaporte" value="Passaporte (Para estrangeiros)" />
            <x-text-input id="passaporte" name="passaporte" type="text" class="mt-1 block w-full" :value="old('passaporte', $user->passaporte)" placeholder="Número do Passaporte" />
            <x-input-error class="mt-2" :messages="$errors->get('passaporte')" />
        </div>

        <!-- ENDEREÇO -->
        <div>
            <x-input-label for="endereco" value="Endereço" />
            <x-text-input id="endereco" name="endereco" type="text" class="mt-1 block w-full" :value="old('endereco', $user->endereco)" />
            <x-input-error class="mt-2" :messages="$errors->get('endereco')" />
        </div>

        <!-- PAÍS E CEP -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="pais" value="País" />
                <x-text-input id="pais" name="pais" type="text" class="mt-1 block w-full" :value="old('pais', $user->pais)" />
                <x-input-error class="mt-2" :messages="$errors->get('pais')" />
            </div>
            <div>
                <x-input-label for="cep" value="CEP" />
                <x-text-input id="cep" name="cep" type="text" class="mt-1 block w-full" :value="old('cep', $user->cep)" />
                <x-input-error class="mt-2" :messages="$errors->get('cep')" />
            </div>
        </div>

        <!-- CIDADE E ESTADO -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="cidade" value="Cidade" />
                <x-text-input id="cidade" name="cidade" type="text" class="mt-1 block w-full" :value="old('cidade', $user->cidade)" />
                <x-input-error class="mt-2" :messages="$errors->get('cidade')" />
            </div>
            <div>
                <x-input-label for="estado" value="Estado" />
                <x-text-input id="estado" name="estado" type="text" class="mt-1 block w-full" :value="old('estado', $user->estado)" />
                <x-input-error class="mt-2" :messages="$errors->get('estado')" />
            </div>
        </div>

        <!-- BOTÃO DE SALVAR -->
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>