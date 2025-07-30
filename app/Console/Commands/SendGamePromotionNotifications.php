<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Game;
use App\Models\Notification;
use App\Events\NewNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SendGamePromotionNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:game-promotions {--min-discount=30 : Desconto mínimo para enviar notificação} {--limit=50 : Máximo de usuários por notificação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia notificações automáticas para usuários quando jogos entram em promoção';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minDiscount = (int) $this->option('min-discount');
        $userLimit = (int) $this->option('limit');
        
        $this->info("🔍 Procurando jogos com desconto mínimo de {$minDiscount}%...");
        
        // Buscar jogos em promoção (assumindo que existe um campo 'discount_percent' e 'sale_price')
        $gamesOnSale = Game::where('discount_percent', '>=', $minDiscount)
            ->where('sale_price', '>', 0)
            ->where('sale_price', '<', DB::raw('normal_price'))
            ->where('is_active', true)
            ->latest('updated_at')
            ->take(10) // Limitar a 10 jogos para não spammar
            ->get();
        
        if ($gamesOnSale->isEmpty()) {
            $this->info("📭 Nenhum jogo encontrado com desconto mínimo de {$minDiscount}%");
            return 0;
        }
        
        $this->info("🎮 Encontrados {$gamesOnSale->count()} jogo(s) em promoção:");
        
        $totalNotificationsSent = 0;
        
        foreach ($gamesOnSale as $game) {
            $this->line("  • {$game->title} - {$game->discount_percent}% OFF");
            
            // Buscar usuários interessados (pode ser baseado em wishlist, categoria, etc.)
            $interestedUsers = User::take($userLimit)->get(); // Simplificado - pode ser melhorado
            
            if ($interestedUsers->isEmpty()) {
                continue;
            }
            
            // Gerar mensagem contextual baseada no jogo
            $message = $this->generateNotificationMessage($game);
            
            $gameNotificationsSent = 0;
            
            foreach ($interestedUsers as $user) {
                try {
                    // Verificar se já enviou notificação recente para este jogo/usuário
                    $recentNotification = Notification::where('user_id', $user->id)
                        ->where('game_id', $game->id)
                        ->where('created_at', '>=', now()->subHours(24))
                        ->exists();
                    
                    if ($recentNotification) {
                        continue; // Não spammar o mesmo usuário sobre o mesmo jogo
                    }
                    
                    $notification = Notification::create([
                        'user_id' => $user->id,
                        'game_id' => $game->id,
                        'message' => $message,
                        'read' => false,
                    ]);
                    
                    // Disparar evento para WebSocket
                    try {
                        event(new NewNotification($notification, $user));
                    } catch (\Exception $e) {
                        Log::warning("Erro WebSocket para usuário {$user->id}: " . $e->getMessage());
                    }
                    
                    $gameNotificationsSent++;
                    $totalNotificationsSent++;
                    
                } catch (\Exception $e) {
                    Log::error("Erro ao criar notificação de promoção para usuário {$user->id}: " . $e->getMessage());
                }
            }
            
            if ($gameNotificationsSent > 0) {
                $this->info("    ✅ {$gameNotificationsSent} notificação(ões) enviada(s)");
            }
        }
        
        if ($totalNotificationsSent > 0) {
            $this->info("🎉 Total: {$totalNotificationsSent} notificação(ões) de promoção enviada(s)!");
        } else {
            $this->comment("📭 Nenhuma notificação nova foi enviada (usuários já notificados recentemente)");
        }
        
        $this->newLine();
        $this->comment('💡 Dica: Este comando pode ser executado automaticamente via cron job');
        $this->comment('📅 Sugestão: Execute a cada hora para capturar novas promoções');
        
        return 0;
    }
    
    /**
     * Gera mensagem contextual baseada no jogo
     */
    private function generateNotificationMessage(Game $game): string
    {
        $messages = [
            // Mensagens genéricas com placeholder
            "🔥 {$game->title} está com {$game->discount_percent}% OFF!",
            "⚡ OFERTA RELÂMPAGO! {$game->title} com desconto de {$game->discount_percent}%",
            "🎯 OPORTUNIDADE! {$game->title} mais barato que nunca - {$game->discount_percent}% OFF",
            "💎 PROMOÇÃO ESPECIAL! {$game->title} com {$game->discount_percent}% de desconto",
            "🚨 ALERTA DE PREÇO! {$game->title} - {$game->discount_percent}% OFF por tempo limitado",
        ];
        
        // Mensagens específicas baseadas no desconto
        if ($game->discount_percent >= 80) {
            $messages = array_merge($messages, [
                "🧨 EXPLOSÃO DE DESCONTO! {$game->title} com {$game->discount_percent}% OFF - Histórico!",
                "💥 MEGA OFERTA! {$game->title} - {$game->discount_percent}% de desconto absurdo!",
                "🎊 CELEBRE! {$game->title} com o maior desconto já visto - {$game->discount_percent}% OFF!",
            ]);
        } elseif ($game->discount_percent >= 50) {
            $messages = array_merge($messages, [
                "🏆 OFERTA PREMIUM! {$game->title} pela metade do preço - {$game->discount_percent}% OFF",
                "🎮 SUPER DESCONTO! {$game->title} com {$game->discount_percent}% OFF - Vale a pena!",
                "⭐ OPORTUNIDADE DE OURO! {$game->title} - {$game->discount_percent}% de desconto",
            ]);
        }
        
        // Adicionar preço se disponível
        if ($game->sale_price && $game->normal_price) {
            $savedAmount = $game->normal_price - $game->sale_price;
            $priceInfo = " - De R$ " . number_format($game->normal_price, 2, ',', '.') . 
                        " por R$ " . number_format($game->sale_price, 2, ',', '.');
            
            // Escolher mensagem aleatória e adicionar preço
            $baseMessage = $messages[array_rand($messages)];
            return $baseMessage . $priceInfo;
        }
        
        return $messages[array_rand($messages)];
    }
}
