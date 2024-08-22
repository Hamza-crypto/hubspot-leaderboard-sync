<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Leaderboard;
use App\Models\WebhookPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WebhookController extends Controller
{
    public function webhook(Request $request)
    {
        // Prepare an array to hold all the records to be inserted
        $webhookEvents = [];

        $time = now();
        foreach ($request->all() as $data) {
            $webhookEvents[] = [
                'object_id' => $data['objectId'],
                'occured_at' => $data['occurredAt'],
                'created_at' => $time,
                'updated_at' => $time,
            ];
        }

        // Insert all records in bulk
        WebhookPayload::insert($webhookEvents);

        // Respond with a 200 status code to acknowledge receipt
        return response()->json(['status' => 'success'], 200);
    }

}
