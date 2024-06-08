<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Leaderboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
        //Cache::forget('dashboard_stats_cache');


        foreach ($request->all() as $data) {
            $customer_id = $data['objectId'];
            $subscriptionType = $data['subscriptionType'];

            if ($subscriptionType == 'contact.deletion') {
                $customer = Customer::where('customer_id', $customer_id)->first();
                if ($customer) {
                    Leaderboard::where('agent', $customer->agent)->delete();
                    $customer->delete();
                }
                continue; // Move to the next item in the loop
            }

            $url = sprintf("objects/contacts/%s?properties=customer_name,firstname,lastname,email,agent,of_applicants,zap_type,status,date", $customer_id);

            $cacheKey = 'hubspot_response_' . $customer_id;

            // Check if the response is cached
            $response = Cache::remember($cacheKey, 5, function () use ($url) {
                return $this->hubspot_controller->call($url, 'GET');
            });


            $this->customer_controller->store($response);
        }

    }
}
