<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Leaderboard;
use Illuminate\Http\Request;

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
            $customer = Customer::where('customer_id', $customer_id)->first();
            Leaderboard::where('agent', $customer->agent)->delete();
            $customer->delete();
            return;
        }


        $url = sprintf("objects/contacts/%s?properties=customer_name,firstname,lastname,email,agent,of_applicants,zap_types", $customer_id);
        $response = $this->hubspot_controller->call($url, 'GET');

        $this->customer_controller->store($response);
    }
}