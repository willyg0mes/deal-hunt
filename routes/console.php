<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Busca de deals — a cada 6h (CheapShark exige User-Agent; ver App\Support\CheapShark)
Schedule::command('games:fetch-deals --limit=200 --free=20')
    ->everySixHours()
    ->withoutOverlapping()
    ->description('Busca automática de deals CheapShark + free RAWG');

// Limpeza de deals expirados (marca inativos / remove conforme comando)
Schedule::command('deals:clean-expired --force')
    ->daily()
    ->at('02:00')
    ->withoutOverlapping()
    ->description('Limpeza automática diária de deals expirados');

// Coleta completa semanal
Schedule::command('collect:deals')
    ->weekly()
    ->sundays()
    ->at('06:00')
    ->withoutOverlapping()
    ->description('Coleta completa semanal de deals');

// Notificações
Schedule::command('notification:game-promotions --min-discount=30 --limit=20')
    ->hourly()
    ->withoutOverlapping()
    ->description('Notificações automáticas para jogos com 30%+ de desconto');

Schedule::command('notification:game-promotions --min-discount=80 --limit=50')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->description('Notificações especiais para mega promoções');

Schedule::command('notification:game-promotions --min-discount=50 --limit=30')
    ->hourlyAt(15)
    ->between('09:00', '22:00')
    ->withoutOverlapping()
    ->description('Notificações de promoções premium durante horário ativo');

Schedule::command('notification:game-promotions --min-discount=25 --limit=40')
    ->everyFifteenMinutes()
    ->weekends()
    ->withoutOverlapping()
    ->description('Notificações intensivas de fim de semana');
