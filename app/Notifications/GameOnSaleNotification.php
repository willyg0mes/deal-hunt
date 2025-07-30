<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class GameOnSaleNotification extends Notification
{
    protected $game;
    protected $discount;

    public function __construct($game, $discount)
    {
        $this->game = $game;
        $this->discount = $discount;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("🔥 O jogo {$this->game->title} está com {$this->discount}% de desconto!")
            ->line("O jogo que você adicionou à sua lista de interesse está em promoção!")
            ->action('Ver promoção', url($this->game->url))
            ->line('Corre lá antes que acabe!');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "🔥 O jogo que você adicionou à wishlist está com {$this->discount}% de desconto!",
            'game_id' => $this->game->id,
            'discount' => $this->discount,
        ];
    }
}
