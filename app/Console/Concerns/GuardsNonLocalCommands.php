<?php

namespace App\Console\Concerns;

trait GuardsNonLocalCommands
{
    protected function ensureSafeEnvironment(): bool
    {
        if (app()->environment(['local', 'testing'])) {
            return true;
        }

        $force = method_exists($this, 'option') ? (bool) $this->option('force') : false;

        if (! $force) {
            $this->error('Comando bloqueado fora de local/testing. Use --force apenas se souber o risco.');

            return false;
        }

        if (app()->environment('production')) {
            return (bool) $this->confirm('Você está em PRODUCTION. Continuar mesmo assim?');
        }

        return true;
    }
}
