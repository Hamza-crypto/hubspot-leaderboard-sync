<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

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
            'tab' => isset($data['properties']['zap_types']) ? $data['properties']['zap_types'] : '',
        ];

        // Check if the customer already exists in the database
        $existingCustomer = Customer::where('customer_id', $customerData['customer_id'])->first();

        if ($existingCustomer) {
            // Update the existing customer's "of_applicants" field
            $existingCustomer->update($customerData);

            dump('Record updated');

        } else {
            // Create a new customer record
            Customer::create($customerData);
            dump('New record created');
        }

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