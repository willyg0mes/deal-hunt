@if ($paginator->hasPages())
    <nav class="flex justify-center mt-6 px-2 sm:px-0">
        <ul class="inline-flex items-center flex-wrap justify-center gap-1 sm:gap-2 max-w-full">
                {{-- Botão Anterior --}}
                @if ($paginator->onFirstPage())
                    <li>
                        <span class="px-2 sm:px-3 py-1.5 sm:py-2 rounded-md bg-gray-800 text-gray-400 cursor-not-allowed text-xs sm:text-sm whitespace-nowrap">
                            <span class="hidden sm:inline">Anterior</span>
                            <span class="sm:hidden">‹</span>
                        </span>
                    </li>
                @else
                    <li>
                        <a href="{{ $paginator->previousPageUrl() }}" class="px-2 sm:px-3 py-1.5 sm:py-2 rounded-md bg-red-700 text-white hover:bg-red-800 transition text-xs sm:text-sm whitespace-nowrap">
                            <span class="hidden sm:inline">Anterior</span>
                            <span class="sm:hidden">‹</span>
                        </a>
                    </li>
                @endif

                {{-- Números das páginas --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li>
                            <span class="px-2 sm:px-3 py-1.5 sm:py-2 rounded-md bg-gray-800 text-gray-400 text-xs sm:text-sm whitespace-nowrap">{{ $element }}</span>
                        </li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li>
                                    <span class="px-2 sm:px-3 py-1.5 sm:py-2 rounded-md bg-red-600 text-white font-bold text-xs sm:text-sm whitespace-nowrap">{{ $page }}</span>
                                </li>
                            @else
                                {{-- No mobile, mostrar apenas páginas próximas à atual ou importantes --}}
                                @php
                                    $currentPage = $paginator->currentPage();
                                    $lastPage = $paginator->lastPage();
                                    // Mostrar: página atual ±1, primeira página, última página
                                    $showOnMobile = abs($page - $currentPage) <= 1 || $page == 1 || $page == $lastPage || ($page == 2 && $currentPage > 3) || ($page == $lastPage - 1 && $currentPage < $lastPage - 2);
                                @endphp
                                <li class="{{ $showOnMobile ? '' : 'hidden sm:inline-flex' }}">
                                    <a href="{{ $url }}" class="px-2 sm:px-3 py-1.5 sm:py-2 rounded-md bg-gray-800 text-white hover:bg-red-700 transition text-xs sm:text-sm whitespace-nowrap">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Botão Próxima --}}
                @if ($paginator->hasMorePages())
                    <li>
                        <a href="{{ $paginator->nextPageUrl() }}" class="px-2 sm:px-3 py-1.5 sm:py-2 rounded-md bg-red-700 text-white hover:bg-red-800 transition text-xs sm:text-sm whitespace-nowrap">
                            <span class="hidden sm:inline">Próxima</span>
                            <span class="sm:hidden">›</span>
                        </a>
                    </li>
                @else
                    <li>
                        <span class="px-2 sm:px-3 py-1.5 sm:py-2 rounded-md bg-gray-800 text-gray-400 cursor-not-allowed text-xs sm:text-sm whitespace-nowrap">
                            <span class="hidden sm:inline">Próxima</span>
                            <span class="sm:hidden">›</span>
                        </span>
                    </li>
                @endif
            </ul>
    </nav>
@endif