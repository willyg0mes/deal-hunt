<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Notification;
use App\Events\NewNotification;
use Illuminate\Support\Facades\Log;

class TestNotificationsByCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:test-category {category? : Categoria da notificação (wishlist, aaa, flash, indie, bundle, event, classic, award)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia notificação teste para todos os usuários baseada em uma categoria específica';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $category = $this->argument('category');
        
        $categories = [
            'wishlist' => [
                '🎯 WISHLIST ALERT! O jogo da sua lista está com 70% OFF!',
                '💝 Presente perfeito! Jogo da sua wishlist em promoção especial',
                '🔔 Notificação personalizada: Seu jogo favorito está mais barato!',
                '⭐ O jogo que você estava esperando finalmente entrou em oferta!',
                '🎮 Atenção! Jogo da sua lista de desejos com desconto imperdível',
            ],
            'aaa' => [
                '🔥 BLOCKBUSTER EM OFERTA! Cyberpunk 2077 com 80% OFF',
                '⚡ RED DEAD REDEMPTION 2 por apenas R$ 59,90!',
                '🎮 GRAND THEFT AUTO V com 75% de desconto!',
                '🏆 THE WITCHER 3 GOTY por preço histórico!',
                '🚀 CALL OF DUTY com mega desconto por tempo limitado!',
            ],
            'flash' => [
                '⚡ FLASH SALE! 6 horas restantes - 90% OFF!',
                '🧨 BOOM! Explosão de descontos por 24h apenas!',
                '⏰ ÚLTIMA HORA! Oferta expira em 2 horas!',
                '💥 BREAKING: Mega promoção surprise por tempo limitado!',
                '🚨 ALERTA VERMELHO! Preços nunca vistos - corre!',
            ],
            'indie' => [
                '💎 INDIE GEMS! Pérolas escondidas com até 95% OFF',
                '🌟 DESCOBERTA INCRÍVEL! Jogo indie premiado em oferta',
                '🎨 ARTE INTERATIVA! Jogos únicos com preços especiais',
                '🧩 PUZZLE PARADISE! Quebra-cabeças incríveis em promoção',
                '🎭 NARRATIVAS ÉPICAS! Stories que vão te emocionar',
            ],
            'bundle' => [
                '📦 MEGA BUNDLE! 10 jogos por R$ 49,90 - Economia de 95%!',
                '💰 PACOTÃO PREMIUM! Coleção completa com desconto histórico',
                '🎮 GAME PASS KILLER! Bundle melhor que assinatura',
                '🏆 GOLD EDITION! Versão completa com 80% de desconto',
                '💎 COLLECTOR\'S DREAM! Edição especial em oferta única',
            ],
            'event' => [
                '🎊 WEEKEND MADNESS! Fim de semana de ofertas insanas',
                '🎈 ANIVERSÁRIO STEAM! Celebre com 85% de desconto',
                '🎄 NATAL CHEGOU CEDO! Presentes com super descontos',
                '☀️ SUMMER SALE! Férias com jogos em promoção',
                '🎆 ANO NOVO, JOGOS NOVOS! Comece 2024 com ofertas incríveis',
            ],
            'classic' => [
                '👾 NOSTALGIA GAMES! Clássicos dos anos 90 em oferta',
                '🕹️ RETRO REVIVAL! Arcade classics com preços modernos',
                '📀 CHILDHOOD MEMORIES! Jogos da sua infância voltaram',
                '🎮 PIXEL ART PARADISE! 8-bit games com descontos 16-bit',
                '🎨 MASTERPIECES! Obras-primas do gaming em promoção',
            ],
            'award' => [
                '🏅 GAME OF THE YEAR! Vencedor do GOTY com 75% OFF',
                '⭐ 10/10 IGN! Jogo perfeito com preço imperfeito',
                '🏆 CRITICS CHOICE! 98% no Metacritic em oferta',
                '👑 PLAYERS FAVORITE! Eleito pelos gamers, aprovado no preço',
                '🎖️ AWARD WINNER! Múltiplos prêmios, um só desconto',
            ]
        ];
        
        if (!$category) {
            $this->info('📂 Categorias disponíveis:');
            foreach (array_keys($categories) as $cat) {
                $this->line("  • {$cat}");
            }
            $this->newLine();
            
            $category = $this->choice(
                '🎯 Escolha uma categoria:',
                array_keys($categories),
                0
            );
        }
        
        if (!isset($categories[$category])) {
            $this->error("❌ Categoria '{$category}' não encontrada!");
            $this->info('📂 Categorias disponíveis: ' . implode(', ', array_keys($categories)));
            return 1;
        }
        
        $messages = $categories[$category];
        $message = $messages[array_rand($messages)];
        
        $users = User::all();
        $totalUsers = $users->count();
        
        if ($totalUsers === 0) {
            $this->error('❌ Nenhum usuário encontrado no sistema.');
            return 1;
        }
        
        $this->info("📢 Enviando notificação da categoria '{$category}' para {$totalUsers} usuário(s)...");
        $this->info("💬 Mensagem: {$message}");
        $this->newLine();
        
        $progressBar = $this->output->createProgressBar($totalUsers);
        $progressBar->start();
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($users as $user) {
            try {
                // Criar notificação no banco
                $notification = Notification::create([
                    'user_id' => $user->id,
                    'game_id' => null,
                    'message' => $message,
                    'read' => false,
                ]);
                
                // Disparar evento para WebSocket (se configurado)
                try {
                    event(new NewNotification($notification, $user));
                } catch (\Exception $e) {
                    Log::warning("Erro ao disparar WebSocket para usuário {$user->id}: " . $e->getMessage());
                }
                
                $successCount++;
                
            } catch (\Exception $e) {
                $errorCount++;
                Log::error("Erro ao criar notificação para usuário {$user->id}: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Relatório final
        if ($successCount > 0) {
            $this->info("✅ {$successCount} notificação(ões) da categoria '{$category}' enviada(s) com sucesso!");
        }
        
        if ($errorCount > 0) {
            $this->error("❌ {$errorCount} erro(s) ao enviar notificações.");
        }
        
        $this->newLine();
        $this->comment('💡 Dica: Usuários logados devem ver a notificação imediatamente via WebSocket.');
        $this->comment("📱 Teste outras categorias: " . implode(', ', array_keys($categories)));
        
        return 0;
    }
}
