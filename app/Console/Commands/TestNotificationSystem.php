<?php

namespace App\Console\Commands;

use App\Console\Concerns\GuardsNonLocalCommands;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;

class TestNotificationSystem extends Command
{
    use GuardsNonLocalCommands;

    protected $signature = 'test:notification-system {--force}';
    protected $description = 'Testa o sistema de notificações em tempo real';

    public function handle()
    {
        if (! $this->ensureSafeEnvironment()) {
            return self::FAILURE;
        }

        $this->info('🔄 Testando sistema de notificações...');
        
        $user = User::first();
        if (!$user) {
            $this->error('❌ Nenhum usuário encontrado');
            return;
        }
        
        $this->info("👤 Usuário: {$user->name}");
        
        // Verificar notificações não lidas
        $unreadCount = $user->notifications()->where('read', false)->count();
        $this->info("📬 Notificações não lidas: {$unreadCount}");
        
        // Verificar notificações recentes (últimos 5 minutos)
        $recentNotifications = $user->notifications()
            ->where('created_at', '>', Carbon::now()->subMinutes(5))
            ->orderBy('created_at', 'desc')
            ->get();
            
        $this->info("🕐 Notificações dos últimos 5 minutos: {$recentNotifications->count()}");
        
        if ($recentNotifications->count() > 0) {
            $this->info("📋 Listando notificações recentes:");
            foreach ($recentNotifications as $notification) {
                $status = $notification->read ? '✅ lida' : '🔔 não lida';
                $this->line("  - [{$notification->id}] {$notification->message} ({$status}) - {$notification->created_at->diffForHumans()}");
            }
        }
        
        // Criar uma nova notificação de teste
        $this->info('');
        $this->info('🧪 Criando notificação de teste...');
        
        $testMessage = '🧪 TESTE SISTEMA: ' . now()->format('H:i:s') . ' - Sistema de polling funcionando!';
        
        $notification = Notification::create([
            'user_id' => $user->id,
            'message' => $testMessage,
            'read' => false,
        ]);
        
        $this->info("✅ Notificação criada: #{$notification->id}");
        $this->info("💬 Mensagem: {$testMessage}");
        
        $this->info('');
        $this->info('🎯 Teste completo! Se o usuário estiver logado no site, deve ver a notificação em até 3 segundos.');
        $this->info('📱 Acesse: http://localhost:8000');
        
        return 0;
    }
}
