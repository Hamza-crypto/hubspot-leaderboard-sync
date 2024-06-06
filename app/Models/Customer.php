<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date'  => 'date:Y-m-d',
    ];

    // Listen for saved event
    protected static function booted()
    {
        static::saved(function ($customer) {
            self::updateLeaderBoard($customer);
        });

        static::deleted(function ($customer) {
            self::updateLeaderBoard($customer);
        });
    }

    public static function updateLeaderBoard($customer)
    {
        // Update or insert entry in leaderboard table

        $agentName = $customer->agent;
        if($agentName == '') {
            return;
        }

        $totalLeads = static::where('agent', $agentName)->whereDate('created_at', Carbon::today())->sum('leads');

        // Find or create leaderboard entry
        $leaderboard = Leaderboard::firstOrNew(['agent' => $agentName]);
        $leaderboard->leads = $totalLeads;
        $leaderboard->tab = $customer->tab;
        $leaderboard->save();
    }
}
