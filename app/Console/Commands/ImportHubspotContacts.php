<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ImportHubspotContacts extends Command
{
    protected $signature = 'import:hubspot-contacts';
    protected $description = 'Import Hubspot contacts from a CSV file';

    public function handle()
    {

        //Clear the table
        Customer::query()->truncate();

        $filename = "all-contacts.csv";
        $filePath = public_path($filename);

        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }
        dump('Opening the file');
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

        Artisan::call('refresh-leaderboard');
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
            'customer_id' => $data[0],
            'name' => $data[1] . ' ' . $data[2],
            'date' => $date,
             'leads' => !empty($data[4]) ? $data[4] : 0,
            'agent' => $data[5] ?? '',
            'email' => '',
            'tab' => 'No Cost ACA',
            'status' => $data[6] ?? 'AOR SWITCH',
            'created_at' => '2024-06-10 00:00:00',
            'updated_at' => '2024-06-10 00:00:00',
        ];
    }

    private function insertBatch(array $batch)
    {
        DB::table('customers')->insert($batch);

        dump(sprintf('%d customers inserted.', count($batch)));

    }

}
