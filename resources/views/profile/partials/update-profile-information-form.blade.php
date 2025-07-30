<section>
    <header>
        <h2 class="text-lg font-semibold text-red-400 dark:text-gray-200">
            {{ __('Informações do Perfil') }}
        </h2>
        <p class="mt-1 text-sm text-gray-300 dark:text-gray-400">
            {{ __("Atualize suas informações de perfil e e-mail.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4 space-y-4">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-sm font-medium text-gray-200 dark:text-gray-300">Nome</label>
            <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}"
                class="mt-1 block w-full rounded-md border-gray-600 dark:border-gray-500 shadow-sm focus:border-red-500 dark:focus:border-gray-400 focus:ring-red-500 dark:focus:ring-gray-400 bg-gray-800 dark:bg-gray-700 text-gray-100 dark:text-gray-200" />
            @error('name')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-200 dark:text-gray-300">E-mail</label>
            <input type="email" name="email" id="email" value="{{ old('email', auth()->user()->email) }}"
                class="mt-1 block w-full rounded-md border-gray-600 dark:border-gray-500 shadow-sm focus:border-red-500 dark:focus:border-gray-400 focus:ring-red-500 dark:focus:ring-gray-400 bg-gray-800 dark:bg-gray-700 text-gray-100 dark:text-gray-200" />
            @error('email')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="currency" class="block text-sm font-medium text-gray-200 dark:text-gray-300">Moeda Preferida</label>
                <select name="currency" id="currency"
                    class="mt-1 block w-full rounded-md border-gray-600 dark:border-gray-500 shadow-sm focus:border-red-500 dark:focus:border-gray-400 focus:ring-red-500 dark:focus:ring-gray-400 bg-gray-800 dark:bg-gray-700 text-gray-100 dark:text-gray-200">
                    @php
                        $currencyService = app(\App\Services\CurrencyConversionService::class);
                        $currencies = $currencyService->getSupportedCurrencies();
                        $userCurrency = old('currency', auth()->user()->currency ?? 'BRL');
                    @endphp
                    @foreach($currencies as $code => $info)
                        <option value="{{ $code }}" {{ $userCurrency === $code ? 'selected' : '' }}>
                            {{ $info['symbol'] }} {{ $info['name'] }} ({{ $code }})
                        </option>
                    @endforeach
                </select>
                @error('currency')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="locale" class="block text-sm font-medium text-gray-200 dark:text-gray-300">Idioma</label>
                <select name="locale" id="locale"
                    class="mt-1 block w-full rounded-md border-gray-600 dark:border-gray-500 shadow-sm focus:border-red-500 dark:focus:border-gray-400 focus:ring-red-500 dark:focus:ring-gray-400 bg-gray-800 dark:bg-gray-700 text-gray-100 dark:text-gray-200">
                    @php
                        $userLocale = old('locale', auth()->user()->locale ?? 'pt-BR');
                    @endphp
                    <option value="pt-BR" {{ $userLocale === 'pt-BR' ? 'selected' : '' }}>🇧🇷 Português (Brasil)</option>
                    <option value="en-US" {{ $userLocale === 'en-US' ? 'selected' : '' }}>🇺🇸 English (US)</option>
                    <option value="es-ES" {{ $userLocale === 'es-ES' ? 'selected' : '' }}>🇪🇸 Español</option>
                </select>
                @error('locale')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-red-600 dark:bg-gray-600 text-white rounded hover:bg-red-700 dark:hover:bg-gray-700 transition-colors">
                Salvar
            </button>
        </div>

        @if (session('status') === 'profile-updated')
            <p class="text-sm text-green-400 mt-2">Perfil atualizado com sucesso!</p>
        @endif
    </form>
</section>
