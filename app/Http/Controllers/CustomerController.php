<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramChannel;

class CustomerController extends Controller
{
    public function store($data)
    {
        // Extract data from the input array
        $customerData = [
            'customer_id' => $data['id'],
            'name' => $this->getFullName($data['properties']),
            'email' => $data['properties']['email'],
            'agent' => isset($data['properties']['agent']) ? $data['properties']['agent'] : '',
            'leads' => isset($data['properties']['of_applicants']) ? (int)$data['properties']['of_applicants'] : 0,
            'tab' => isset($data['properties']['zap_types']) ? $data['properties']['zap_types'] : 'No Cost ACA',
        ];

        // Check if the customer already exists in the database
        $existingCustomer = Customer::where('customer_id', $customerData['customer_id'])->first();

        if ($existingCustomer) {
            // Update the existing customer's "of_applicants" field
            $existingCustomer->update($customerData);

            $data_array['msg'] = sprintf('Customer updated: %s %s', $customerData['agent'], $customerData['leads']);


            dump('Record updated');

        } else {
            // Create a new customer record
            Customer::create($customerData);

            $data_array['msg'] = sprintf('New customer created: %s %s', $customerData['agent'], $customerData['leads']);

            dump('New record created');
        }
        Notification::route(TelegramChannel::class, '')->notify(new GeneralNotification($data_array));

    }


    private function getFullName($properties)
    {
        if($properties['customer_name'] != null) {
            return $properties['customer_name'];
        }

        $firstName = isset($properties['firstname']) ? $properties['firstname'] : '';
        $lastName = isset($properties['lastname']) ? $properties['lastname'] : '';

        return trim($firstName . ' ' . $lastName);
    }
}