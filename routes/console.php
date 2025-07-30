<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Agendamento para busca automática diária de deals
Schedule::command('games:fetch-deals --limit=100')
    ->daily()
    ->at('08:00')
    ->description('Busca automática diária de 100 deals');

// Agendamento para limpeza automática de deals expirados
Schedule::command('deals:clean-expired')
    ->daily()
    ->at('02:00')
    ->description('Limpeza automática diária de deals expirados');

// Agendamento semanal para coleta completa de deals
Schedule::command('collect:deals')
    ->weekly()
    ->sundays()
    ->at('06:00')
    ->description('Coleta completa semanal de todos os deals disponíveis');

// 🔔 NOTIFICAÇÕES AUTOMÁTICAS DE PROMOÇÕES 🔔

// Notificações gerais (desconto 30%+) - A cada hora
Schedule::command('notification:game-promotions --min-discount=30 --limit=20')
    ->hourly()
    ->description('Notificações automáticas para jogos com 30%+ de desconto');

// Mega promoções (desconto 80%+) - A cada 30 minutos
Schedule::command('notification:game-promotions --min-discount=80 --limit=50')
    ->everyThirtyMinutes()
    ->description('Notificações especiais para mega promoções');

// Promoções premium (50%+) durante horário ativo
Schedule::command('notification:game-promotions --min-discount=50 --limit=30')
    ->hourlyAt(15) // A cada hora, aos 15 minutos
    ->between('09:00', '22:00')
    ->description('Notificações de promoções premium durante horário ativo');

// Verificação intensiva de fim de semana
Schedule::command('notification:game-promotions --min-discount=25 --limit=40')
    ->everyFifteenMinutes()
    ->weekends()
    ->description('Notificações intensivas de fim de semana');
