<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\DiscordAlerts\Facades\DiscordAlert;

class GuruFetchNewJobs extends Command
{
    protected $signature = 'guru:fetch-new-jobs';

    protected $description = 'Fetch new jobs from Guru.com';

    public function handle()
    {
        #Add guru controller
        $guru = new \App\Http\Controllers\GuruController();
        $guru->store_jobs();

        $guru->delete_old_jobs();
        return Command::SUCCESS;
    }
}
