<?php

namespace App\Http\Controllers;

use App\Models\AirTable;
use App\Models\Cursor;
use App\Notifications\AirTableNotification;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramChannel;

class HubspotController extends Controller
{
    public function call($endpoint, $method = 'GET', $body = [])
    {
        $url = sprintf(
            '%s/%s',
            env('HUBSPOT_BASE_URL'),
            $endpoint
        );

        $response = Http::withToken(env('HUBSPOT_TOKEN'));

        if ($method === 'GET') {
            $response = $response->get($url);
        }

        return $response->json();
    }

}
