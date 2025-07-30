<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-red-400 dark:text-gray-200">
            {{ __('Excluir Conta') }}
        </h2>

        <p class="mt-1 text-sm text-gray-300 dark:text-gray-400">
            {{ __('Uma vez que sua conta for excluída, todos os seus recursos e dados serão permanentemente deletados. Antes de excluir sua conta, por favor baixe qualquer dado ou informação que você deseja manter.') }}
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors"
    >{{ __('Excluir Conta') }}</button>

    <div 
        x-data="{ show: false }" 
        x-on:open-modal.window="$event.detail == 'confirm-user-deletion' ? show = true : null" 
        x-on:close.stop="show = false" 
        x-on:keydown.escape.window="show = false" 
        x-show="show" 
        class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50" 
        style="display: none;"
    >
        <div x-show="show" class="fixed inset-0 transform transition-all" x-on:click="show = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
        </div>

        <div x-show="show" class="mb-6 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-md sm:mx-auto" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                @csrf
                @method('delete')

                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('Tem certeza que deseja excluir sua conta?') }}
                </h2>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Uma vez que sua conta for excluída, todos os seus recursos e dados serão permanentemente deletados. Por favor, digite sua senha para confirmar que você gostaria de excluir permanentemente sua conta.') }}
                </p>

                <div class="mt-6">
                    <label for="password" class="sr-only">{{ __('Senha') }}</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="mt-1 block w-3/4 rounded-md border-gray-600 dark:border-gray-500 shadow-sm focus:border-red-500 dark:focus:border-gray-400 focus:ring-red-500 dark:focus:ring-gray-400 bg-gray-800 dark:bg-gray-700 text-gray-100 dark:text-gray-200"
                        placeholder="{{ __('Senha') }}"
                    />
                    @error('password', 'userDeletion')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" x-on:click="show = false" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors mr-3">
                        {{ __('Cancelar') }}
                    </button>

                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                        {{ __('Excluir Conta') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
