<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTestAdminUser extends Command
{
    protected $signature = 'admin:create-test-user
                            {--email= : E-mail do admin (obrigatório fora de local)}
                            {--name=Admin : Nome do usuário}
                            {--password= : Senha (gerada aleatoriamente se omitida)}
                            {--force : Permitir execução fora de local/testing}';

    protected $description = 'Cria ou atualiza um usuário admin (bloqueado em produção sem --force)';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing']) && ! $this->option('force')) {
            $this->error('Comando bloqueado fora de local/testing. Use --force apenas se souber o risco.');

            return self::FAILURE;
        }

        if (app()->environment('production') && $this->option('force')) {
            if (! $this->confirm('Você está em PRODUCTION. Continuar mesmo assim?')) {
                $this->warn('Cancelado.');

                return self::FAILURE;
            }
        }

        $email = $this->option('email') ?: (app()->environment(['local', 'testing']) ? 'admin@test.com' : null);
        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Informe um --email válido.');

            return self::FAILURE;
        }

        $password = $this->option('password');
        if (! is_string($password) || $password === '') {
            $password = Str::password(20);
            $this->warn('Senha gerada automaticamente (guarde agora — não será exibida de novo nos logs).');
        }

        if (strlen($password) < 12) {
            $this->error('Senha deve ter no mínimo 12 caracteres.');

            return self::FAILURE;
        }

        $user = User::query()->firstOrNew(['email' => $email]);
        $created = ! $user->exists;

        $user->name = (string) ($this->option('name') ?: 'Admin');
        $user->password = Hash::make($password);
        $user->email_verified_at = $user->email_verified_at ?? now();
        $user->is_admin = true;
        $user->save();

        $this->info($created ? 'Usuário administrador criado.' : 'Usuário administrador atualizado.');
        $this->table(['Campo', 'Valor'], [
            ['Email', $email],
            ['Senha', $password],
            ['É Admin', 'Sim'],
            ['URL Admin', url('/admin')],
        ]);

        return self::SUCCESS;
    }
}
