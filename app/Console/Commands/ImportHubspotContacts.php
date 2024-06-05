<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportHubspotContacts extends Command
{
    protected $signature = 'import:hubspot-contacts';
    protected $description = 'Import Hubspot contacts from a CSV file';

    public function handle()
    {
        $filename = "all-contacts.csv";
        $filePath = public_path($filename);

        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        // Open the CSV file for reading
        $file = fopen($filePath, 'r');

        // Skip the header row
        fgetcsv($file);

        $batchSize = 1000; // Adjust batch size as needed

        $batch = [];
        while (($data = fgetcsv($file)) !== false) {
            $batch[] = $this->mapRecord($data);

            if (count($batch) >= $batchSize) {
                $this->insertBatch($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->insertBatch($batch);
        }

        fclose($file);

        $this->info('Import completed successfully.');
        return 0;
    }

    private function mapRecord(array $data)
    {
        $date = !empty($data[3]) ? $data[3] : null;
        if ($date !== null) {
            $date = date_create_from_format('Y-m-d', $date);
            if (!$date) {
                $date = null; // Set date to null if it's not a valid format
            }
        }

        // Map CSV fields to database fields
        return [
            'customer_id' => $data[0], // Assuming 'Record ID' is the first column
            'name' => $data[1] . ' ' . $data[2], // Assuming 'First Name' and 'Last Name' are in columns 1 and 2
            'email' => $data[11], // Assuming 'Email' is in column 3
            'agent' => $data[10] ?? '', // Assuming 'AGENT' is in column 4
            'leads' => $data[4] ?? 0, // Assuming '#' is in column 5
            'tab' => 'No Cost ACA', // Assuming default value
            'status' => $data[7] ?? 'AOR SWITCH', // Assuming 'Lead Status' is in column 6
            'date' => $date // Assign formatted date or null
        ];
    }

    private function insertBatch(array $batch)
    {
        DB::table('customers')->insert($batch);
        $this->info(sprintf('%d customers inserted.', count($batch)));
    }

}
