<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Leaderboard;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RefreshLeaderboard extends Command
{
    protected $signature = 'refresh-leaderboard';
    protected $description = 'Regenerates leaderboard entries';

    public function handle()
    {
        //Clear the table
        Leaderboard::truncate();


        // Fetch today's leads grouped by agent and tab
        $todayLeads = Customer::selectRaw('agent, tab,  SUM(leads) as total_leads')
            ->whereDate('date', Carbon::today())
            ->groupBy('agent', 'tab')
            ->get();

        //dd($todayLeads);
        // Insert fresh entries into the leaderboard table
        foreach ($todayLeads as $lead) {
            Leaderboard::create([
                'agent' => $lead->agent,
                'leads' => $lead->total_leads,
                'tab'   => $lead->tab,
            ]);
        }

        $this->info('Sync completed successfully.');
        return 0;
    }




}
