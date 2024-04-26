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
            'agent' => isset($data['properties']['agent']) ? $data['properties']['agent'] : null,
            'leads' => isset($data['properties']['of_applicants']) ? (int)$data['properties']['of_applicants'] : 0,
        ];

        // Check if the customer already exists in the database
        $existingCustomer = Customer::where('email', $customerData['email'])->first();

        if ($existingCustomer) {
            // Update the existing customer's "of_applicants" field
            $existingCustomer->update(['leads' => $customerData['leads']]);
        } else {
            // Create a new customer record
            Customer::create($customerData);
        }

    }


    private function getFullName($properties)
    {
        $firstName = isset($properties['firstname']) ? $properties['firstname'] : '';
        $lastName = isset($properties['lastname']) ? $properties['lastname'] : '';

        return trim($firstName . ' ' . $lastName);
    }
}
