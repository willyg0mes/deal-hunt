{{--
  Filtros unificados do catálogo.
  Props:
    - $action (string): rota do form GET
    - $stores (iterable): nomes de lojas
    - $categories (iterable)
    - $ajax (bool): true = Alpine live na home; false = form tradicional
    - $showFree (bool): checkbox "só grátis"
    - $showDiscount (bool): min_discount
    - $showPrice (bool): min/max price
--}}
@php
    $ajax = $ajax ?? false;
    $showFree = $showFree ?? true;
    $showDiscount = $showDiscount ?? true;
    $showPrice = $showPrice ?? true;
    $stores = $stores ?? ($storeNames ?? collect());
    $inputClass = 'w-full bg-gray-800/80 border border-gray-600/80 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500/80 focus:border-transparent';
    $labelClass = 'block text-xs font-medium text-gray-400 mb-1';
@endphp

@if($ajax)
<div
    x-data="{
        search: @js(request('search', '')),
        store: @js(request('store', '')),
        category: @js(request('category', '')),
        min_discount: @js(request('min_discount', '')),
        min_price: @js(request('min_price', '')),
        max_price: @js(request('max_price', '')),
        sort: @js(request('sort', 'discount_desc')),
        free: @js(request('free') === '1'),
        loading: false,
        openAdvanced: {{ request()->hasAny(['min_price', 'max_price', 'min_discount']) ? 'true' : 'false' }},
        updateFilters() {
            this.loading = true;
            const params = new URLSearchParams();
            if (this.search) params.set('search', this.search);
            if (this.store) params.set('store', this.store);
            if (this.category) params.set('category', this.category);
            if (this.min_discount !== '' && this.min_discount !== null) params.set('min_discount', this.min_discount);
            if (this.min_price !== '' && this.min_price !== null) params.set('min_price', this.min_price);
            if (this.max_price !== '' && this.max_price !== null) params.set('max_price', this.max_price);
            if (this.sort && this.sort !== 'discount_desc') params.set('sort', this.sort);
            if (this.free) params.set('free', '1');
            const qs = params.toString();
            const url = qs ? `/?${qs}` : '/';
            history.replaceState({}, '', url);
            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            })
            .then(r => r.text())
            .then(html => {
                const el = document.getElementById('games-list');
                if (el) el.innerHTML = html;
                this.loading = false;
            })
            .catch(() => { this.loading = false; });
        },
        clearFilters() {
            this.search = '';
            this.store = '';
            this.category = '';
            this.min_discount = '';
            this.min_price = '';
            this.max_price = '';
            this.sort = 'discount_desc';
            this.free = false;
            this.updateFilters();
        }
    }"
    class="mb-6"
>
    <div class="rounded-xl border border-gray-700/60 bg-gradient-to-br from-gray-900/90 to-gray-800/60 p-4 sm:p-5 shadow-lg shadow-black/30">
        <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
            <div>
                <h2 class="text-lg font-bold text-white tracking-tight">Explorar ofertas</h2>
                <p class="text-xs text-gray-400 mt-0.5">Busca, loja, categoria, preço e ordenação</p>
            </div>
            <button type="button" @click="clearFilters()" class="text-xs text-gray-400 hover:text-white underline-offset-2 hover:underline">
                Limpar filtros
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <label class="{{ $labelClass }}">Buscar</label>
                <input x-model="search" @input.debounce.400="updateFilters()" type="search" placeholder="Nome do jogo..." class="{{ $inputClass }}" />
            </div>
            <div>
                <label class="{{ $labelClass }}">Categoria</label>
                <select x-model="category" @change="updateFilters()" class="{{ $inputClass }}">
                    <option value="">Todas</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->slug }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $labelClass }}">Loja</label>
                <select x-model="store" @change="updateFilters()" class="{{ $inputClass }}">
                    <option value="">Todas</option>
                    @foreach($stores as $storeName)
                        <option value="{{ $storeName }}">{{ $storeName }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-3">
            <button type="button" @click="openAdvanced = !openAdvanced" class="text-xs font-medium text-red-400 hover:text-red-300">
                <span x-text="openAdvanced ? 'Ocultar opções' : 'Mais opções'"></span>
            </button>
            <label class="inline-flex items-center gap-2 text-xs text-gray-300 cursor-pointer select-none">
                <input type="checkbox" x-model="free" @change="updateFilters()" class="rounded border-gray-600 bg-gray-800 text-red-600 focus:ring-red-500" />
                Só grátis
            </label>
            <div class="ml-auto flex items-center gap-2">
                <label class="{{ $labelClass }} mb-0">Ordenar</label>
                <select x-model="sort" @change="updateFilters()" class="{{ $inputClass }} w-auto min-w-[10rem]">
                    <option value="discount_desc">Maior desconto</option>
                    <option value="price_asc">Menor preço</option>
                    <option value="price_desc">Maior preço</option>
                    <option value="title_asc">Nome A–Z</option>
                    <option value="newest">Mais recentes</option>
                </select>
            </div>
        </div>

        <div x-show="openAdvanced" x-cloak class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 border-t border-gray-700/50 pt-4">
            @if($showDiscount)
            <div>
                <label class="{{ $labelClass }}">Desconto mín. (%)</label>
                <input x-model="min_discount" @input.debounce.400="updateFilters()" type="number" min="0" max="100" placeholder="ex: 50" class="{{ $inputClass }}" />
            </div>
            @endif
            @if($showPrice)
            <div>
                <label class="{{ $labelClass }}">Preço mín.</label>
                <input x-model="min_price" @input.debounce.400="updateFilters()" type="number" min="0" step="0.01" placeholder="0" class="{{ $inputClass }}" />
            </div>
            <div>
                <label class="{{ $labelClass }}">Preço máx.</label>
                <input x-model="max_price" @input.debounce.400="updateFilters()" type="number" min="0" step="0.01" placeholder="100" class="{{ $inputClass }}" />
            </div>
            @endif
        </div>

        <div x-show="loading" class="flex items-center justify-center gap-2 text-gray-300 mt-4">
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm">Buscando…</span>
        </div>
    </div>
</div>
@else
<form method="GET" action="{{ $action }}" class="mb-6 rounded-xl border border-gray-700/60 bg-gradient-to-br from-gray-900/90 to-gray-800/60 p-4 sm:p-5 shadow-lg shadow-black/30">
    <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
        <div>
            <h2 class="text-lg font-bold text-white tracking-tight">Filtros</h2>
            <p class="text-xs text-gray-400 mt-0.5">Refine a busca</p>
        </div>
        @if(request()->hasAny(\App\Support\GameCatalogQuery::filterKeys()))
            <a href="{{ $action }}" class="text-xs text-gray-400 hover:text-white underline-offset-2 hover:underline">Limpar filtros</a>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="sm:col-span-2">
            <label for="search" class="{{ $labelClass }}">Buscar</label>
            <input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="Nome do jogo..." class="{{ $inputClass }}" />
        </div>
        <div>
            <label for="category" class="{{ $labelClass }}">Categoria</label>
            <select id="category" name="category" class="{{ $inputClass }}">
                <option value="">Todas</option>
                @foreach($categories as $category)
                    <option value="{{ $category->slug }}" @selected(request('category') == $category->slug || request('category') == $category->name)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="store" class="{{ $labelClass }}">Loja</label>
            <select id="store" name="store" class="{{ $inputClass }}">
                <option value="">Todas</option>
                @foreach($stores as $storeName)
                    <option value="{{ $storeName }}" @selected(request('store') == $storeName)>{{ $storeName }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        @if($showDiscount)
        <div>
            <label for="min_discount" class="{{ $labelClass }}">Desconto mín. (%)</label>
            <input type="number" id="min_discount" name="min_discount" min="0" max="100" value="{{ request('min_discount') }}" placeholder="ex: 50" class="{{ $inputClass }}" />
        </div>
        @endif
        @if($showPrice)
        <div>
            <label for="min_price" class="{{ $labelClass }}">Preço mín.</label>
            <input type="number" id="min_price" name="min_price" min="0" step="0.01" value="{{ request('min_price') }}" placeholder="0" class="{{ $inputClass }}" />
        </div>
        <div>
            <label for="max_price" class="{{ $labelClass }}">Preço máx.</label>
            <input type="number" id="max_price" name="max_price" min="0" step="0.01" value="{{ request('max_price') }}" placeholder="100" class="{{ $inputClass }}" />
        </div>
        @endif
        <div>
            <label for="sort" class="{{ $labelClass }}">Ordenar</label>
            <select id="sort" name="sort" class="{{ $inputClass }}">
                <option value="discount_desc" @selected(request('sort', 'discount_desc') === 'discount_desc')>Maior desconto</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>Menor preço</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>Maior preço</option>
                <option value="title_asc" @selected(request('sort') === 'title_asc')>Nome A–Z</option>
                <option value="newest" @selected(request('sort') === 'newest')>Mais recentes</option>
            </select>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        @if($showFree)
        <label class="inline-flex items-center gap-2 text-sm text-gray-300 cursor-pointer select-none">
            <input type="checkbox" name="free" value="1" @checked(request('free') === '1') class="rounded border-gray-600 bg-gray-800 text-red-600 focus:ring-red-500" />
            Só grátis
        </label>
        @endif
        <button type="submit" class="ml-auto bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2 rounded-lg text-sm transition">
            Aplicar filtros
        </button>
    </div>
</form>
@endif
