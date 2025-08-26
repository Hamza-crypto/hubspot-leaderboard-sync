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
        DB::table('customers')->truncate();
        $filename = "all-contacts.csv";
        $filePath = storage_path($filename);

        if (!file_exists($filePath)) {
            dump("File not found: $filePath");
            return 1;
        }
        dump('Opening the file');
        // Open the CSV file for reading
        $file = fopen($filePath, 'r');

        // Skip the header row
        fgetcsv($file);

        $batchSize = 1000; // Adjust batch size as needed


        //Clear the table
        Customer::query()->truncate();

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

    private function format_timestamp($date)
    {
        $date = date_create_from_format('d/m/Y H:i', $date);
        $mysqlDate = $date ? $date->format('Y-m-d H:i:s') : null;
        return $mysqlDate;
    }

    private function mapRecord(array $data)
    {
        $date = !empty($data[6]) ? $data[6] : null;
        if ($date !== null) {
            $date = date_create_from_format('d/m/Y', $date);
            if (!$date) {
                $date = now(); // Set date to null if it's not a valid format
            }
        }

        // Map CSV fields to database fields
        return [
            'customer_id' => isset($data[0]) ? trim((string) $data[0]) : null,
            'name' => $data[1] . ' ' . $data[2],
            'date' => $date,
            'leads' => 0,
            'agent' => $data[4] ?? '',
            'email' => '',
            'tab' => '',
            'status' => $data[7] ?? 'Unknown',
            'created_at' => $this->format_timestamp($data[5]),
            'updated_at' => $this->format_timestamp($data[8]),
        ];
    }

    private function insertBatch(array $batch)
    {
        DB::table('customers')->insert($batch);

        dump(sprintf('%d customers inserted.', count($batch)));

    }

}
