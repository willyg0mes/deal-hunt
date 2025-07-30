<?php

namespace App\Observers;

use App\Models\Game;
use App\Services\TranslationService;
use Illuminate\Support\Facades\Log;

class GameTranslationObserver
{
    protected $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Handle the Game "created" event.
     */
    public function created(Game $game): void
    {
        // Verificar se o jogo tem descrição e se contém texto em inglês
        if ($this->shouldTranslate($game)) {
            try {
                // Executar tradução de forma assíncrona ou em background
                // Para não atrasar a criação do jogo
                $this->scheduleTranslation($game);
                
                Log::info("🌐 Jogo '{$game->title}' (ID: {$game->id}) marcado para tradução automática");
                
            } catch (\Exception $e) {
                Log::error("❌ Erro ao agendar tradução para jogo '{$game->title}' (ID: {$game->id}): " . $e->getMessage());
            }
        }
    }

    /**
     * Handle the Game "updated" event.
     */
    public function updated(Game $game): void
    {
        // Se a descrição foi alterada, verificar se precisa de nova tradução
        if ($game->wasChanged('description') && $this->shouldTranslate($game)) {
            try {
                $this->scheduleTranslation($game, true); // Force update
                
                Log::info("🔄 Descrição do jogo '{$game->title}' (ID: {$game->id}) alterada - reagendando tradução");
                
            } catch (\Exception $e) {
                Log::error("❌ Erro ao reagendar tradução para jogo '{$game->title}' (ID: {$game->id}): " . $e->getMessage());
            }
        }
    }

    /**
     * Verifica se o jogo deve ser traduzido
     */
    private function shouldTranslate(Game $game): bool
    {
        // Verificar se há descrição
        if (empty($game->description)) {
            return false;
        }

        // Verificar se já tem tradução (a menos que seja força)
        if ($game->translations()->where('field_name', 'description')->exists()) {
            return false;
        }

        // Verificar se contém texto em inglês
        return $this->containsEnglishText($game->description);
    }

    /**
     * Detecta se o texto contém palavras em inglês
     */
    private function containsEnglishText(string $text): bool
    {
        // Palavras comuns em inglês que indicam necessidade de tradução
        $englishWords = [
            'the', 'and', 'you', 'that', 'was', 'for', 'are', 'with', 'his', 'they',
            'this', 'have', 'from', 'not', 'been', 'has', 'her', 'more', 'can',
            'said', 'each', 'which', 'them', 'into', 'time', 'will', 'about',
            'game', 'play', 'player', 'world', 'adventure', 'action', 'story',
            'character', 'battle', 'hero', 'journey', 'quest', 'epic', 'fight',
            'gameplay', 'multiplayer', 'single', 'campaign', 'mission', 'level',
            'experience', 'explore', 'discover', 'unlock', 'collect', 'build'
        ];

        $text = strtolower($text);
        $wordCount = 0;

        foreach ($englishWords as $word) {
            // Procurar pela palavra como palavra completa
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $text)) {
                $wordCount++;
            }
        }

        // Se encontrar 3 ou mais palavras em inglês, considera que precisa tradução
        return $wordCount >= 3;
    }

    /**
     * Agenda a tradução do jogo
     */
    private function scheduleTranslation(Game $game, bool $force = false): void
    {
        // Em um ambiente de produção, isso seria enviado para uma fila (queue)
        // Por enquanto, vamos executar diretamente, mas de forma que não trave
        
        try {
            // Executar a tradução
            $translation = $this->translationService->translateGameDescription($game, $force);
            
            if ($translation) {
                Log::info("✅ Jogo '{$game->title}' traduzido automaticamente com método '{$translation->translation_method}' e confiança {$translation->confidence_score}");
            } else {
                Log::warning("⚠️ Não foi possível traduzir automaticamente o jogo '{$game->title}'");
            }
            
        } catch (\Exception $e) {
            Log::error("❌ Erro na tradução automática do jogo '{$game->title}': " . $e->getMessage());
            
            // Opcional: Agendar para tentar novamente mais tarde
            // Aqui poderia usar um job para retry
        }
    }

    /**
     * Handle the Game "deleted" event.
     */
    public function deleted(Game $game): void
    {
        // Quando um jogo é deletado, suas traduções também devem ser removidas
        // O relacionamento cascade do banco deve cuidar disso, mas podemos fazer log
        Log::info("🗑️ Jogo '{$game->title}' (ID: {$game->id}) deletado - traduções removidas automaticamente");
    }
}
