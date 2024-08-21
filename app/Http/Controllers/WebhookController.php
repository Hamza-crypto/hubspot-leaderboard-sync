<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Leaderboard;
use App\Models\WebhookPayload;
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
        // Prepare an array to hold all the records to be inserted
        $webhookEvents = [];

        $time = now();
        foreach ($request->all() as $data) {
            $webhookEvents[] = [
                'object_id' => $data['objectId'],
                'created_at' => $time,
                'updated_at' => $time,
            ];
        }

        // Insert all records in bulk
        WebhookPayload::insert($webhookEvents);

        // Respond with a 200 status code to acknowledge receipt
        return response()->json(['status' => 'success'], 200);
    }

    public function webhook2(Request $request)
    {

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

            $url = sprintf("objects/contacts/%s?properties=%s", $customer_id, env('HUBSPOT_PROPERTIES'));

            $cacheKey = 'hubspot_response_' . $customer_id;

            // Check if the response is cached
            $response = Cache::remember($cacheKey, 0, function () use ($url) {
                return $this->hubspot_controller->call($url, 'GET');
            });


            $this->customer_controller->store($response);
        }

    }
}
