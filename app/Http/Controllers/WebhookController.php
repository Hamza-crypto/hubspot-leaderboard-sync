<?php

namespace App\Http\Controllers;

use App\Notifications\AirTableNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramChannel;

class WebhookController extends Controller
{
    public $hubspot_controller;
    public $customer_controller;

    public function __construct()
    {
        $this->hubspot_controller = new HubspotController();
        $this->customer_controller = new CustomerController();
    }

    public function webhook(Request $request)
    {
        $data = $request->all()[0];

        $customer_id = $data['objectId'];

        $url = sprintf("objects/contacts/%s?properties=firstname,lastname,email,agent,of_applicants", $customer_id);

        $response = $this->hubspot_controller->call($url, 'GET');

        $this->customer_controller->store($response);
    }
}
