<?php

namespace App\Observers;

use App\Models\Game;
use App\Models\Notification;
use App\Events\NewNotification;
use App\Services\RawgService;

class GameObserver
{
    protected RawgService $rawgService;

    public function __construct(RawgService $rawgService)
    {
        $this->rawgService = $rawgService;
    }

    public function creating(Game $game): void
    {
        // Desabilitado durante seeding
        if (app()->runningInConsole()) {
            return;
        }

        // Verificar se a imagem precisa ser melhorada
        if ($this->needsImageImprovement($game->image)) {
            $betterImage = $this->rawgService->getBestGameImage($game->title);
            if ($betterImage) {
                $game->image = $betterImage;
            }
        }
    }

    public function saved(Game $game): void
    {
        // Desabilitado durante seeding
        if (app()->runningInConsole()) {
            return;
        }

        // Se não tem desconto, não notifica
        if ($game->discount_percent <= 0) return;

        // Para cada usuário que tem este jogo na wishlist
        foreach ($game->wishlistedBy as $user) {
            // Verifica se o usuário já foi notificado recentemente (últimas 24h)
            $alreadyNotified = $user->notifications()
                ->where('game_id', $game->id)
                ->where('created_at', '>=', now()->subDay())
                ->exists();

            if (!$alreadyNotified) {
                $notification = Notification::create([
                    'user_id' => $user->id,
                    'game_id' => $game->id,
                    'message' => "🎉 O jogo '{$game->title}' está com {$game->discount_percent}% de desconto na {$game->store_name}!",
                ]);

                // Dispara o evento de notificação em tempo real
                broadcast(new NewNotification($notification, $user));
            }
        }
    }

    /**
     * Verifica se a imagem precisa ser melhorada
     */
    private function needsImageImprovement(?string $image): bool
    {
        if (!$image) return true;

        $badPatterns = [
            'placeholder',
            'screenshot',
            'shot',
            'clip'
        ];

        $imageLower = strtolower($image);
        foreach ($badPatterns as $pattern) {
            if (str_contains($imageLower, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
