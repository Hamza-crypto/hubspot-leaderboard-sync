<?php

namespace App\Console\Commands;

use App\Http\Controllers\AirTableController;
use App\Http\Controllers\CrispController;
use App\Models\AirTable;
use App\Notifications\AirTableNotification;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramChannel;

class UpdateDataOnCrisp extends Command
{
    protected $signature = 'crisp:update';

    protected $description = 'Command description';

    public function handle()
    {
        $air_table_controller = new AirTableController;
        $crisp_controller = new CrispController();

        $webhooks = \DB::table('air_tables')
            ->selectRaw('DISTINCT record, base')
            ->take(10)
            ->get();

        foreach ($webhooks as $webhook) {
            try {
                $url = sprintf('%s', $webhook->record);
                if($webhook->base == 'rak'){
                    $data = $air_table_controller->call_rak($url);
                }
                else{
                    $data = $air_table_controller->call($url);
                }

                $data = $data['fields'];

                if (! isset($data['Email'])) {
                    AirTable::where('record', $webhook->record)->delete();
                    return 0;
                }

                $email = trim($data['Email']);

                $this->createNewContact($crisp_controller, $data, $email);
                $this->updateContactInfo($crisp_controller, $data, $email);
                $this->updateProfileInfo($crisp_controller, $data, $email, $webhook->base, $air_table_controller);

                AirTable::where('record', $webhook->record)->delete();

                $data_array['msg'] = sprintf('Data updated on CRISP %s', $email);
                Notification::route(TelegramChannel::class, '')->notify(new AirTableNotification($data_array));
            } catch (Exception $e) {
                AirTable::where('record', $webhook->record)->delete();
                dump($e->getMessage());
            }
        }

    }

    public function createNewContact($crisp_controller, $data, $email)
    {
        $url = sprintf('people/profile/%s', $email);
        $response = $crisp_controller->call($url);

        //Create new contact if not found on Crisp
        if ($response['error']) {

            $body['email'] = $email;

            if (isset($data['Full Name'])) {
                $body['person']['nickname'] = $data['Full Name'];
            }

            if (isset($data['Phone'])) {
                $body['person']['phone'] = $data['Phone'];
            }

            if (isset($data['Gender'])) {
                $body['person']['gender'] = strtolower($data['Gender']);
            }

            $url = sprintf('people/profile');
            $response = $crisp_controller->call($url, 'POST', $body);
            dump($response);
        }

    }

    public function updateContactInfo($crisp_controller, $data, $email)
    {
        if (isset($data['Full Name'])) {
            $body['person']['nickname'] = $data['Full Name'];
        }

        if (isset($data['Phone'])) {
            $body['person']['phone'] = $data['Phone'];
        }

        if (isset($data['Gender'])) {
            $body['person']['gender'] = strtolower($data['Gender']);
        }

        $body['segments'][] = 'airtable';

        //Uncomment this for inserting status value into Segments

        // if(isset($data['Status'])){
        //     $body['segments'][] = $data['Status'];
        // }

        $url = sprintf('people/profile/%s', $email);
        $response = $crisp_controller->call($url, 'PATCH', $body);
        dump($response);
    }

    public function updateProfileInfo($crisp_controller, $data, $email, $branch, $air_table_controller)
    {
        if (isset($data['whatsapp'])) {
            $body['data']['whatsapp_business_number'] = $data['whatsapp'];
        }

        if (isset($data['Course interest'])) {
            if (is_array($data['Course interest'])) {
                $body['data']['course_interest'] = implode(', ', $data['Course interest']);
            } else {
                $body['data']['course_interest'] = $data['Course interest'];
            }
        } else {
            $body['data']['course_interest'] = '';
        }

        $body['data']['nationality'] = isset($data['Nationality']) ? $data['Nationality'] : '';
        $body['data']['registered'] = isset($data['Registered']) ? $data['Registered'] : '';
        $body['data']['preferred_timing'] = isset($data['Preferred timing']) ? $data['Preferred timing'] : '';
        $body['data']['total_spend'] = isset($data['Amount Rollup (from Deal)']) ? $data['Amount Rollup (from Deal)'] : '';
        $body['data']['branch'] = $branch == 'sales' ? 'Dubai' : 'Ras Al Khaimah'; // isset($data['Branch']) ? $data['Branch'] : '';

        $body['data']['utm_campaign'] = isset($data['utm_campaign']) ? $data['utm_campaign'] : '';
        $body['data']['utm_source'] = isset($data['utm_source']) ? $data['utm_source'] : '';
        $body['data']['utm_term'] = isset($data['utm_term']) ? $data['utm_term'] : '';

        $body['data']['GCLID'] = isset($data['GCLID']) ? $data['GCLID'] : '';

        $body['data']['status'] = isset($data['Status']) ? $data['Status'] : '';

        $body['data']['Discount_offered'] = $this->get_discount($air_table_controller, $data);

        $url = sprintf('people/data/%s', $email);
        $response = $crisp_controller->call($url, 'PATCH', $body);

        dump($response);
    }

    public function get_discount($air_table_controller, $data)
    {
        $discount = '';

        if (isset($data['Discount offered']) && is_array($data['Discount offered'])) {

                $discount_id = $data['Discount offered'][0];

                $url = sprintf('%s', $discount_id);
                $response = $air_table_controller->call($url);

                // Extract name and discount amount
                $name = $response['fields']['Discount Name'];

                // Check if 'Discount Amount' exists in the response
                if (isset($decoded_response['fields']['Discount Amount'])) {
                    $discount_amount = $decoded_response['fields']['Discount Amount'] * 100; // Convert to percentage
                    // Prepare discount string with amount
                    $discount = "$name";
                } else {
                    // Prepare discount string without amount
                    $discount = $name;
                }

        }

        return $discount;
    }

}
