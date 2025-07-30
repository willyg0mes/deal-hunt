<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CreateTestAdminUser extends Command
{
    protected $signature = 'admin:create-test-user';
    protected $description = 'Create a test admin user and show login info';

    public function handle()
    {
        // Limpar rate limiting
        $this->call('cache:clear');
        
        $user = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Test',
                'password' => bcrypt('admin123'),
                'email_verified_at' => now(),
                'is_admin' => true
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->info('✅ Usuário administrador criado com sucesso!');
        } else {
            $this->info('✅ Usuário administrador já existe.');
        }

        $this->table(['Campo', 'Valor'], [
            ['Email', 'admin@test.com'],
            ['Senha', 'admin123'],
            ['É Admin', $user->is_admin ? 'Sim' : 'Não'],
            ['URL Admin', url('/admin')]
        ]);

        return 0;
    }
}
