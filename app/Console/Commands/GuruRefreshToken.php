<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GuruRefreshToken extends Command
{
    protected $signature = 'guru:token-refresh';

    protected $description = 'It fetches new access token from Guru API and updates the .env file.';

    public function handle()
    {
        $guru = new \App\Http\Controllers\GuruController();
        $guru->getNewAccessTokenFromRefreshToken();

        return Command::SUCCESS;
    }
}
