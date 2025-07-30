<section>
    <header>
        <h2 class="text-lg font-medium text-red-400 dark:text-gray-200">
            {{ __('Atualizar Senha') }}
        </h2>

        <p class="mt-1 text-sm text-gray-300 dark:text-gray-400">
            {{ __('Certifique-se de que sua conta está usando uma senha longa e aleatória para manter-se segura.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-medium text-gray-200 dark:text-gray-300">{{ __('Senha Atual') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" 
                class="mt-1 block w-full rounded-md border-gray-600 dark:border-gray-500 shadow-sm focus:border-red-500 dark:focus:border-gray-400 focus:ring-red-500 dark:focus:ring-gray-400 bg-gray-800 dark:bg-gray-700 text-gray-100 dark:text-gray-200" 
                autocomplete="current-password" />
            @error('current_password', 'updatePassword')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-medium text-gray-200 dark:text-gray-300">{{ __('Nova Senha') }}</label>
            <input id="update_password_password" name="password" type="password" 
                class="mt-1 block w-full rounded-md border-gray-600 dark:border-gray-500 shadow-sm focus:border-red-500 dark:focus:border-gray-400 focus:ring-red-500 dark:focus:ring-gray-400 bg-gray-800 dark:bg-gray-700 text-gray-100 dark:text-gray-200" 
                autocomplete="new-password" />
            @error('password', 'updatePassword')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-medium text-gray-200 dark:text-gray-300">{{ __('Confirmar Senha') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" 
                class="mt-1 block w-full rounded-md border-gray-600 dark:border-gray-500 shadow-sm focus:border-red-500 dark:focus:border-gray-400 focus:ring-red-500 dark:focus:ring-gray-400 bg-gray-800 dark:bg-gray-700 text-gray-100 dark:text-gray-200" 
                autocomplete="new-password" />
            @error('password_confirmation', 'updatePassword')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="px-4 py-2 bg-red-600 dark:bg-gray-600 text-white rounded hover:bg-red-700 dark:hover:bg-gray-700 transition-colors">{{ __('Salvar') }}</button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-400"
                >{{ __('Salvo.') }}</p>
            @endif
        </div>
    </form>
</section>
