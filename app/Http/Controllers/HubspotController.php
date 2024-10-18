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
            if($response->status() == 404) {
                return null;
            }
        }
        // Get the headers from the response
        $headers = $response->headers();

        $rateLimitRemaining = isset($headers['x-hubspot-ratelimit-remaining'][0]) ? (int) $headers['x-hubspot-ratelimit-remaining'][0] : null;
        $secondlyRateLimitRemaining = isset($headers['x-hubspot-ratelimit-secondly-remaining'][0]) ? (int) $headers['x-hubspot-ratelimit-secondly-remaining'][0] : null;

        if ($rateLimitRemaining < 3) {
            dump('I am sleeping');
            sleep(5);
        }
        elseif ($secondlyRateLimitRemaining < 3) {
            dump('I am sleeping 2');
            sleep(5);
        }
        return $response->json();
    }

}
