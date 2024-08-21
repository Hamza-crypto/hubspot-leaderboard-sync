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
        // Fetch unique object IDs from the WebhookPayload table
        $events = WebhookPayload::select('object_id')
            ->distinct()
            ->groupBy('object_id')
            ->limit(5)
            ->get();

        // Instantiate controllers
        $hubspotController = new HubspotController();
        $customerController = new CustomerController();

        // Array to store object IDs to be deleted later
        $objectIdsToDelete = [];

        foreach ($events as $event) {
            $object_id = $event->object_id;

            // Construct the HubSpot API URL
            $url = sprintf("objects/contacts/11%s?properties=%s", $object_id, env('HUBSPOT_PROPERTIES'));

            // Call the HubSpot API
            $response = $hubspotController->call($url, 'GET');

            // If the response is empty or invalid, delete the associated customer
            if (!$response) {
                $customer = Customer::where('customer_id', $object_id)->first();
                if ($customer) {
                    Leaderboard::where('agent', $customer->agent)->delete();
                    $customer->delete();
                }
                // Mark this object ID for deletion from WebhookPayload
                $objectIdsToDelete[] = $object_id;
                continue;
            }

            // Store the response using the CustomerController
            $customerController->store($response);

            // Mark this object ID for deletion from WebhookPayload
            $objectIdsToDelete[] = $object_id;
        }

        // Bulk delete the processed object IDs from WebhookPayload
        WebhookPayload::whereIn('object_id', $objectIdsToDelete)->delete();
    }
}
