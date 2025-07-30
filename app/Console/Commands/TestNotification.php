<?php

namespace App\Console\Commands;

use App\Events\NewNotification;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notification {user_id? : ID do usuário}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia uma notificação de teste para um usuário';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id') ?? User::first()?->id;
        
        if (!$userId) {
            $this->error('Nenhum usuário encontrado no banco de dados!');
            return 1;
        }

        $user = User::find($userId);
        
        if (!$user) {
            $this->error("Usuário com ID {$userId} não encontrado!");
            return 1;
        }

        $notification = Notification::create([
            'user_id' => $user->id,
            'game_id' => null,
            'message' => '🧨 Teste de notificação em tempo real! Sistema funcionando perfeitamente.',
        ]);

        // Dispara a notificação em tempo real
        broadcast(new NewNotification($notification, $user));

        $this->info("Notificação de teste enviada para {$user->name} (ID: {$user->id})");
        $this->info("Verifique o navegador se a notificação apareceu em tempo real!");

        return 0;
    }
}
