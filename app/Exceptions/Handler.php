<?php

namespace App\Exceptions;

use App\Notifications\ExceptionOccurredNotification;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function report(Throwable $exception)
    {
        if ($this->shouldReport($exception)) {
            $this->sendTelegramNotification($exception);
        }

        parent::report($exception);
    }

    private function sendTelegramNotification(Throwable $exception)
    {
        //Notification::route(TelegramChannel::class, '')->notify(new ExceptionOccurredNotification($exception));
    }
}
