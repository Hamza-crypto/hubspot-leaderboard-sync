<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Leaderboard;
use App\Models\WebhookPayload;
use Illuminate\Console\Command;
use App\Http\Controllers\HubspotController;
use App\Http\Controllers\CustomerController;

class ProcessWebhookEvents extends Command
{
    protected $signature = 'webhook:process';
    protected $description = 'Process stored webhook events';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $processed_ids = [];
        // Fetch unique object IDs from the WebhookPayload table
        $events = WebhookPayload::limit(15)
            ->get();

        // Instantiate controllers
        $hubspotController = new HubspotController();
        $customerController = new CustomerController();

        foreach ($events as $event) {
            $object_id = $event->object_id;

            if(in_array($object_id, $processed_ids)) { //if this object is laready processed in this run,  then ignore it.
                continue;
            }

            $processed_ids[] = $object_id;

            // Construct the HubSpot API URL
            $url = sprintf("objects/contacts/%s?properties=%s", $object_id, env('HUBSPOT_PROPERTIES'));

            // Call the HubSpot API
            $response = $hubspotController->call($url, 'GET');

            WebhookPayload::where('object_id', $object_id)->where('occured_at', $event->occured_at)->delete();

            // If the response is empty or invalid, delete the associated customer
            if (!$response) {
                $customer = Customer::where('customer_id', $object_id)->first();
                if ($customer) {
                    Leaderboard::where('agent', $customer->agent)->delete();
                    $customer->delete();
                }
                continue;
            }

            // Store the response using the CustomerController
            $customerController->store($response);
        }
    }
}
