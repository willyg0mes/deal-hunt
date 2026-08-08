<?php

namespace App\Console\Commands;

use App\Console\Concerns\GuardsNonLocalCommands;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Notification;
use App\Events\NewNotification;
use Illuminate\Support\Facades\Log;

class SendTestNotificationToAll extends Command
{
    use GuardsNonLocalCommands;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:test-all {--message= : Mensagem personalizada para a notificação} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia uma notificação teste para todos os usuários do sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->ensureSafeEnvironment()) {
            return self::FAILURE;
        }

        $customMessage = $this->option('message');
        
        $defaultMessages = [
            // 🎯 Wishlist & Interesse Pessoal
            '🎯 WISHLIST ALERT! O jogo da sua lista está com 70% OFF!',
            '💝 Presente perfeito! Jogo da sua wishlist em promoção especial',
            '🔔 Notificação personalizada: Seu jogo favorito está mais barato!',
            '⭐ O jogo que você estava esperando finalmente entrou em oferta!',
            '🎮 Atenção! Jogo da sua lista de desejos com desconto imperdível',
            
            // 🔥 Jogos Famosos & AAA
            '🔥 BLOCKBUSTER EM OFERTA! Cyberpunk 2077 com 80% OFF',
            '⚡ RED DEAD REDEMPTION 2 por apenas R$ 59,90!',
            '🎮 GRAND THEFT AUTO V com 75% de desconto!',
            '🏆 THE WITCHER 3 GOTY por preço histórico!',
            '🚀 CALL OF DUTY com mega desconto por tempo limitado!',
            '🎯 ASSASSIN\'S CREED mais barato que nunca!',
            '💎 BATTLEFIELD 2042 com 85% OFF - Última chance!',
            '🔫 COUNTER-STRIKE skins em promoção especial!',
            
            // ⚡ Flash Sales & Ofertas Relâmpago
            '⚡ FLASH SALE! 6 horas restantes - 90% OFF!',
            '🧨 BOOM! Explosão de descontos por 24h apenas!',
            '⏰ ÚLTIMA HORA! Oferta expira em 2 horas!',
            '� BREAKING: Mega promoção surprise por tempo limitado!',
            '🚨 ALERTA VERMELHO! Preços nunca vistos - corre!',
            '⚡ LIGHTNING DEAL! Jogo premium por R$ 9,99!',
            
            // 💎 Indies & Jogos Especiais
            '💎 INDIE GEMS! Pérolas escondidas com até 95% OFF',
            '🌟 DESCOBERTA INCRÍVEL! Jogo indie premiado em oferta',
            '🎨 ARTE INTERATIVA! Jogos únicos com preços especiais',
            '🧩 PUZZLE PARADISE! Quebra-cabeças incríveis em promoção',
            '🎭 NARRATIVAS ÉPICAS! Stories que vão te emocionar',
            '🔮 EXPERIÊNCIAS ÚNICAS! Jogos experimentais em oferta',
            
            // 🏷️ Categorias Específicas
            '🏎️ VELOCIDADE MÁXIMA! Jogos de corrida com 80% OFF',
            '⚔️ BATALHAS ÉPICAS! RPGs medievais em promoção',
            '🚀 ESPAÇO SIDERAL! Jogos sci-fi com descontos galácticos',
            '🧟 SURVIVAL HORROR! Sustos garantidos com preços baixos',
            '� ESTRATÉGIA REAL! Conquiste mundos com 70% de desconto',
            '🏟️ ESPORTES 2024! Simuladores esportivos em oferta',
            
            // 🎊 Eventos & Sazonais
            '🎊 WEEKEND MADNESS! Fim de semana de ofertas insanas',
            '🎈 ANIVERSÁRIO STEAM! Celebre com 85% de desconto',
            '🎃 HALLOWEEN HORROR! Jogos de terror com preços assombrados',
            '🎄 NATAL CHEGOU CEDO! Presentes com super descontos',
            '🎆 ANO NOVO, JOGOS NOVOS! Comece 2024 com ofertas incríveis',
            '☀️ SUMMER SALE! Férias com jogos em promoção',
            
            // 💰 Ofertas Especiais & Bundles
            '📦 MEGA BUNDLE! 10 jogos por R$ 49,90 - Economia de 95%!',
            '💰 PACOTÃO PREMIUM! Coleção completa com desconto histórico',
            '🎮 GAME PASS KILLER! Bundle melhor que assinatura',
            '� GOLD EDITION! Versão completa com 80% de desconto',
            '💎 COLLECTOR\'S DREAM! Edição especial em oferta única',
            
            // 🚨 Urgência & Escassez
            '🚨 ÚLTIMAS UNIDADES! Oferta limitada - apenas 100 keys!',
            '⏳ CONTAGEM REGRESSIVA! 3... 2... 1... OFERTA!',
            '🔥 ESTOQUE ACABANDO! Garante o seu antes que termine',
            '💨 VOANDO! Milhares já compraram - não fique de fora',
            '🎯 EXCLUSIVO! Oferta especial só para você',
            
            // 🎮 Clássicos & Nostalgia
            '👾 NOSTALGIA GAMES! Clássicos dos anos 90 em oferta',
            '🕹️ RETRO REVIVAL! Arcade classics com preços modernos',
            '📀 CHILDHOOD MEMORIES! Jogos da sua infância voltaram',
            '🎮 PIXEL ART PARADISE! 8-bit games com descontos 16-bit',
            '🎨 MASTERPIECES! Obras-primas do gaming em promoção',
            
            // 🏅 Premiados & Aclamados
            '🏅 GAME OF THE YEAR! Vencedor do GOTY com 75% OFF',
            '⭐ 10/10 IGN! Jogo perfeito com preço imperfeito',
            '🏆 CRITICS CHOICE! 98% no Metacritic em oferta',
            '👑 PLAYERS FAVORITE! Eleito pelos gamers, aprovado no preço',
            '🎖️ AWARD WINNER! Múltiplos prêmios, um só desconto',
            
            // 🔮 Novidades & Pré-vendas
            '🆕 JUST RELEASED! Lançamento com desconto de estreia',
            '🔮 PRÉ-VENDA! Garante seu jogo antes do lançamento',
            '🚀 EARLY ACCESS! Seja um dos primeiros a jogar',
            '⚡ DAY ONE! Jogo fresquinho com preço quente',
            '🎬 FROM CINEMA! Do filme para o game com oferta especial'
        ];
        
        $message = $customMessage ?: $defaultMessages[array_rand($defaultMessages)];
        
        $users = User::all();
        $totalUsers = $users->count();
        
        if ($totalUsers === 0) {
            $this->error('❌ Nenhum usuário encontrado no sistema.');
            return 1;
        }
        
        $this->info("📢 Enviando notificação teste para {$totalUsers} usuário(s)...");
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
            $this->info("✅ {$successCount} notificação(ões) enviada(s) com sucesso!");
        }
        
        if ($errorCount > 0) {
            $this->error("❌ {$errorCount} erro(s) ao enviar notificações.");
        }
        
        $this->newLine();
        $this->comment('💡 Dica: Usuários logados no sistema devem ver a notificação imediatamente via WebSocket.');
        $this->comment('📱 Acesse o site e teste os botões: 🧪 (API Test), 🔊 (Sound Test), 💬 (Toast Test)');
        
        return 0;
    }
}
