<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GameDiscountNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('🔔 Jogo em promoção!')
            ->line("🔥 O jogo \"{$this->game->title}\" que você adicionou à sua lista de interesse está com {$this->game->discount_percent}% de desconto!")
            ->action('Ver jogo', url("/jogos/{$this->game->id}"));
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "🔥 O jogo \"{$this->game->title}\" que você adicionou à wishlist está com {$this->game->discount_percent}% de desconto!"
        ];
    }
}
