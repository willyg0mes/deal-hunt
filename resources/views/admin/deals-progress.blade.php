@extends('admin.layout')

@section('title', 'Progresso da Busca')
@section('header', 'Busca de Deals em Progresso')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-red-100 p-6 hover:shadow-lg transition-all duration-200">
    <div class="text-center">
        <div class="mb-6">
            <div class="relative inline-block">
                <svg class="animate-spin h-12 w-12 text-red-600 mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div class="absolute inset-0 bg-red-600 rounded-full opacity-20 animate-ping"></div>
            </div>
        </div>
        
        <h3 class="text-xl font-semibold mb-2 text-gray-900 flex items-center justify-center">
            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Buscando novos deals...
        </h3>
        <p class="text-gray-600 mb-6">Por favor, aguarde enquanto processamos as ofertas da API CheapShark.</p>
        
        <div class="w-full bg-red-100 rounded-full h-4 mb-6 border border-red-200">
            <div id="progressBar" class="bg-gradient-to-r from-red-500 to-red-600 h-4 rounded-full transition-all duration-300 shadow-sm" style="width: 0%"></div>
        </div>
        
        <div id="status" class="text-sm text-gray-600 mb-6 p-3 bg-red-50/50 rounded-lg border border-red-100">
            Iniciando busca...
        </div>
        
        <div id="stats" class="grid grid-cols-3 gap-4 text-center">
            <div class="bg-red-50/30 p-4 rounded-lg border border-red-100">
                <div class="text-2xl font-bold text-red-600" id="processed">0</div>
                <div class="text-sm text-gray-600 font-medium">Processados</div>
            </div>
            <div class="bg-red-50/30 p-4 rounded-lg border border-red-100">
                <div class="text-2xl font-bold text-red-600" id="added">0</div>
                <div class="text-sm text-gray-600 font-medium">Adicionados</div>
            </div>
            <div class="bg-red-50/30 p-4 rounded-lg border border-red-100">
                <div class="text-2xl font-bold text-red-600" id="updated">0</div>
                <div class="text-sm text-gray-600 font-medium">Atualizados</div>
            </div>
        </div>
        
        <div class="mt-8">
            <button onclick="window.location.href='{{ route('admin.dashboard') }}'" 
                    class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled id="backButton">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Voltar ao Dashboard
            </button>
        </div>
    </div>
</div>

<script>
let progress = 0;
let processed = 0;
let added = 0;
let updated = 0;

function updateProgress() {
    // Simular progresso (em uma implementação real, você faria polling via AJAX)
    if (progress < 100) {
        progress += Math.random() * 10;
        processed += Math.floor(Math.random() * 3);
        
        if (Math.random() > 0.7) {
            added += 1;
        }
        
        if (Math.random() > 0.8) {
            updated += 1;
        }
        
        document.getElementById('progressBar').style.width = Math.min(progress, 100) + '%';
        document.getElementById('processed').textContent = processed;
        document.getElementById('added').textContent = added;
        document.getElementById('updated').textContent = updated;
        
        if (progress < 50) {
            document.getElementById('status').textContent = 'Conectando com a API CheapShark...';
        } else if (progress < 80) {
            document.getElementById('status').textContent = 'Processando ofertas e atualizando imagens...';
        } else if (progress < 100) {
            document.getElementById('status').textContent = 'Finalizando e salvando dados...';
        }
        
        setTimeout(updateProgress, 1000 + Math.random() * 2000);
    } else {
        document.getElementById('status').textContent = 'Busca concluída com sucesso!';
        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('backButton').disabled = false;
        document.getElementById('backButton').className = 'px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition';
        
        // Redirecionar automaticamente após 3 segundos
        setTimeout(() => {
            window.location.href = '{{ route('admin.dashboard') }}';
        }, 3000);
    }
}

// Iniciar simulação de progresso
setTimeout(updateProgress, 1000);
</script>
@endsection
