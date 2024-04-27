<?php

namespace App\Http\Controllers;

use App\Models\Customer;
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

        $subscriptionType = $data['subscriptionType'];

        if($subscriptionType == 'contact.deletion') {
            Customer::where('customer_id', $customer_id)->delete();
            return;
        }


        $url = sprintf("objects/contacts/%s?properties=customer_name,firstname,lastname,email,agent,of_applicants,zap_types", $customer_id);
        $response = $this->hubspot_controller->call($url, 'GET');

        $this->customer_controller->store($response);
    }
}