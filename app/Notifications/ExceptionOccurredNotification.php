<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use Throwable;

class ExceptionOccurredNotification extends Notification
{
    protected $exception;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        $telegram_id = env('TELEGRAM_HUBSPOT');
        $msg = sprintf("%s \nLine:%s", $this->exception->getMessage(), $this->exception->getLine());

        return TelegramMessage::create()
            ->to($telegram_id)
            ->content($msg);

    }
}