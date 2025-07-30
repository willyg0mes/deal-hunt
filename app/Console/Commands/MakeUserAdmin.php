<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class MakeUserAdmin extends Command
{
    protected $signature = 'user:make-admin {email}';
    protected $description = 'Make a user administrator by email';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("Usuário com email {$email} não encontrado.");
            return 1;
        }
        
        $user->is_admin = true;
        $user->save();
        
        $this->info("Usuário {$user->name} ({$email}) foi promovido a administrador.");
        return 0;
    }
}
