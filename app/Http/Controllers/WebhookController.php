<?php

namespace App\Http\Controllers;

use App\Notifications\AirTableNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramChannel;

class WebhookController extends Controller
{
    public $hubspot_controller;

    public function __construct()
    {
        $this->hubspot_controller = new HubspotController();
    }

    public function webhook(Request $request)
    {
        $data = $request->all()[0];


        $customer_id = $data['objectId'];

        $url = sprintf("objects/contacts/%s?properties=of_applicants,email,agent", $customer_id);

        $response = $this->hubspot_controller->call($url, 'GET');

        dd($response);
        // Check if response status is 200 and it contains the "id" field
        // if (isset($response['id'])) {

        //     $data_array['msg'] = sprintf('Webhook successfuly sent for user: %s', $response['fields']['Email']);
        //     Notification::route(TelegramChannel::class, '')->notify(new AirTableNotification($data_array));

        //     return 'Record successfully created in AirTable';
        // } else {
        //     return $response; // Return appropriate message if creation failed
        // }

    }
}
