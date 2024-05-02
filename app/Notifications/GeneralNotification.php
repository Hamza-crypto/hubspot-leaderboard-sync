<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class GeneralNotification extends Notification
{
    use Queueable;

    public $msg = [];

    public function __construct($msg = [])
    {
        $this->msg = $msg;
    }

    public function via($notifiable)
    {
        return [TelegramChannel::class];
    }

    public function toTelegram($notifiable)
    {
        $msg = $this->msg;
        $telegram_id = env('TELEGRAM_HUBSPOT');

        return TelegramMessage::create()
        // Optional recipient user id.
            ->to($telegram_id)
            ->content($msg['msg']);

    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
