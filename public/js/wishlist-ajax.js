document.addEventListener('DOMContentLoaded', function() {
    console.log('Wishlist AJAX script carregado');
    
    // ========== WISHLIST AJAX ==========
    
    // Função para atualizar visual do botão
    function updateWishlistButton(button, isInWishlist) {
        const icon = button.querySelector('.wishlist-icon');
        
        if (isInWishlist) {
            button.className = button.className.replace(/bg-gray-\d+/, 'bg-yellow-500').replace(/text-gray-\d+/, 'text-white').replace(/hover:bg-gray-\d+/, 'hover:bg-yellow-600');
            button.title = 'Remover da Wishlist';
            
            if (icon) {
                // Para ícones na lista de jogos
                if (button.closest('.inline-block')) {
                    icon.innerHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 715.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>';
                } else {
                    // Para página de detalhes
                    icon.innerHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 715.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg><span class="hidden sm:inline">Remover da</span> Wishlist';
                }
            }
        } else {
            button.className = button.className.replace(/bg-yellow-\d+/, 'bg-gray-200').replace(/text-white/, 'text-gray-700').replace(/hover:bg-yellow-\d+/, 'hover:bg-gray-300');
            button.title = 'Adicionar à Wishlist';
            
            if (icon) {
                // Para ícones na lista de jogos
                if (button.closest('.inline-block')) {
                    icon.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>';
                } else {
                    // Para página de detalhes
                    icon.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg><span class="hidden sm:inline">Adicionar à</span> Wishlist';
                }
            }
        }
        
        button.dataset.inWishlist = isInWishlist;
    }
    
    // Função para reinicializar eventos wishlist em novos elementos
    function initWishlistEvents() {
        document.querySelectorAll('.wishlist-form:not([data-ajax-init])').forEach(form => {
            form.setAttribute('data-ajax-init', 'true');
            console.log('Inicializando wishlist para novo elemento:', form);
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const button = this.querySelector('.wishlist-btn');
                const gameId = this.dataset.gameId;
                
                button.disabled = true;
                button.style.opacity = '0.6';
                
                fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateWishlistButton(button, data.isInWishlist);
                        
                        // Para a página de wishlist, remover o item se foi removido
                        if (window.location.pathname.includes('wishlist') && !data.isInWishlist) {
                            const gameCard = this.closest('.bg-white');
                            if (gameCard) {
                                gameCard.remove();
                                
                                const remainingItems = document.querySelectorAll('.wishlist-form').length;
                                if (remainingItems === 0) {
                                    location.reload();
                                }
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    this.submit();
                })
                .finally(() => {
                    button.disabled = false;
                    button.style.opacity = '1';
                });
            });
        });
    }
    
    // Inicializar eventos wishlist na primeira carga
    initWishlistEvents();
    
    // ========== FILTERS AJAX ==========
    
    // Lidar com filtros AJAX
    const filtersForm = document.getElementById('filters-form');
    if (filtersForm) {
        console.log('Formulário de filtros encontrado');
        
        filtersForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Submit de filtros interceptado para AJAX');
            
            const formData = new FormData(this);
            const filterBtn = document.getElementById('filter-btn');
            const filterText = filterBtn.querySelector('.filter-text');
            const filterLoading = filterBtn.querySelector('.filter-loading');
            const resultsContainer = document.getElementById('games-results');
            
            // Mostrar loading
            filterText.classList.add('hidden');
            filterLoading.classList.remove('hidden');
            filterBtn.disabled = true;
            
            // Converter FormData para URLSearchParams para requisição GET
            const params = new URLSearchParams();
            for (let [key, value] of formData.entries()) {
                if (value.trim() !== '') {
                    params.append(key, value);
                }
            }
            
            const url = this.action + '?' + params.toString();
            console.log('Fazendo requisição para:', url);
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => {
                console.log('Resposta dos filtros recebida:', response);
                return response.text();
            })
            .then(html => {
                console.log('HTML recebido');
                
                // Criar um documento temporário para extrair apenas a seção de resultados
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                // Extrair a seção de resultados
                const newResults = tempDiv.querySelector('#games-results');
                if (newResults) {
                    resultsContainer.innerHTML = newResults.innerHTML;
                    
                    // Reinicializar eventos wishlist para os novos elementos
                    initWishlistEvents();
                    
                    // Atualizar URL sem recarregar
                    const newUrl = new URL(window.location);
                    newUrl.search = params.toString();
                    window.history.pushState({}, '', newUrl);
                    
                    console.log('Filtros aplicados com sucesso!');
                } else {
                    console.error('Seção de resultados não encontrada na resposta');
                    // Fallback: recarregar página
                    window.location.href = url;
                }
            })
            .catch(error => {
                console.error('Erro AJAX nos filtros:', error);
                // Fallback: submeter formulário normalmente
                console.log('Fallback: submetendo formulário normalmente');
                this.removeEventListener('submit', arguments.callee);
                this.submit();
            })
            .finally(() => {
                // Restaurar botão
                filterText.classList.remove('hidden');
                filterLoading.classList.add('hidden');
                filterBtn.disabled = false;
            });
        });
        
        // Auto-submit nos selects (opcional - para UX ainda melhor)
        const categorySelect = document.getElementById('category');
        const storeSelect = document.getElementById('store');
        
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                console.log('Categoria alterada, submetendo filtros automaticamente');
                filtersForm.dispatchEvent(new Event('submit'));
            });
        }
        
        if (storeSelect) {
            storeSelect.addEventListener('change', function() {
                console.log('Loja alterada, submetendo filtros automaticamente');
                filtersForm.dispatchEvent(new Event('submit'));
            });
        }
    }
});
