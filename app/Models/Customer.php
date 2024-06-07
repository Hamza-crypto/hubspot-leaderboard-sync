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
            self::removeDuplicateEntries($customer);
            self::updateLeaderBoard($customer);
        });

        static::deleted(function ($customer) {
            self::updateLeaderBoard($customer);
        });
    }


    public static function removeDuplicateEntries($customer)
    {
        // Find all entries for the given customer_id except the most recent one
        $duplicates = static::where('customer_id', $customer->customer_id)
            ->where('id', '<>', $customer->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        // If there are duplicates, delete them
        if ($duplicates->count() > 0) {
            foreach ($duplicates as $duplicate) {
                $duplicate->delete();
            }
        }
    }


    public static function updateLeaderBoard($customer)
    {
        // Update or insert entry in leaderboard table

        $agentName = $customer->agent;
        if($agentName == '') {
            return;
        }

        $totalLeads = static::where('agent', $agentName)->whereDate('date', Carbon::today())->sum('leads');

        // Find or create leaderboard entry
        $leaderboard = Leaderboard::firstOrNew(['agent' => $agentName]);
        $leaderboard->leads = $totalLeads;
        $leaderboard->tab = $customer->tab;
        $leaderboard->save();
    }
}