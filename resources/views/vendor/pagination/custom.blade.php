@if ($paginator->hasPages())
    <nav class="flex justify-center mt-6">
        <ul class="inline-flex items-center space-x-1">
            {{-- Botão Anterior --}}
            @if ($paginator->onFirstPage())
                <li>
                    <span class="px-3 py-2 rounded bg-gray-800 text-gray-400 cursor-not-allowed">Anterior</span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 rounded bg-red-700 text-white hover:bg-red-800 transition">Anterior</a>
                </li>
            @endif

            {{-- Números das páginas --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li>
                        <span class="px-3 py-2 rounded bg-gray-800 text-gray-400">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li>
                                <span class="px-3 py-2 rounded bg-red-600 text-white font-bold">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}" class="px-3 py-2 rounded bg-gray-800 text-white hover:bg-red-700 transition">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Botão Próxima --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 rounded bg-red-700 text-white hover:bg-red-800 transition">Próxima</a>
                </li>
            @else
                <li>
                    <span class="px-3 py-2 rounded bg-gray-800 text-gray-400 cursor-not-allowed">Próxima</span>
                </li>
            @endif
        </ul>
    </nav>
@endif