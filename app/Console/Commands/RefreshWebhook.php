<?php

namespace App\Console\Commands;

use App\Http\Controllers\AirTableController;
use Illuminate\Console\Command;

class RefreshWebhook extends Command
{
    protected $signature = 'airtable:refresh-webhook';

    protected $description = 'Command description';

    public function handle()
    {
        $air_table_controller = new AirTableController;

        $url = sprintf('bases/%s/webhooks/%s/refresh', env('BASE_ID'), env('WEBHOOK_ID'));
        $response = $air_table_controller->call($url, 'POST');

        dump($response);
    }
}
