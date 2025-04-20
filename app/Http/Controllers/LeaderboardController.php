<?php

namespace App\Http\Controllers;

use App\Models\Leaderboard;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function index()
    {
        // --- Define Time Ranges ---
        
        $testDateString = '2024-08-21 12:51:42';
        $fixedNow = Carbon::parse($testDateString); // Create Carbon instance from the string

        // 1. Daily: The specific day we are testing (start of that day)
        $today = $fixedNow->copy()->startOfDay(); // Gets 2024-08-21 00:00:00

        // 2. Weekly: Start of the week containing the test date, up to the test date/time
        // Carbon::MONDAY ensures the week starts on Monday
        $startOfWeek = $fixedNow->copy()->startOfWeek(Carbon::MONDAY); // Gets Monday of the week of 2024-08-21

        // 3. Monthly: Start of the month containing the test date, up to the test date/time
        $startOfMonth = $fixedNow->copy()->startOfMonth(); // Gets 2024-08-01 00:00:00

        // Use the $fixedNow variable as the end point for ranges
        $endDateForRanges = $fixedNow;

        $baseQuery = function ($startDate, $endDate) {
            return DB::table('customers')
                ->select('agent', DB::raw('COUNT(*) as customer_count'))
                ->whereNotNull('agent')
                ->where('agent', '!=', '')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->groupBy('agent')
                ->orderByDesc('customer_count')
                ->limit(20); // Base limit, can be overridden
        };

        $dailyLeaders = DB::table('customers')
            ->select('agent', DB::raw('COUNT(*) as customer_count'))
            ->whereNotNull('agent')
            ->where('agent', '!=', '')
             // Use whereDate to match only the date part of the fixed date
            ->whereDate('created_at', $today) // Filters for records updated on 2024-08-21
            ->groupBy('agent')
            ->orderByDesc('customer_count')
            ->limit(10)
            ->get();

       // 2. Weekly Leaders (Using the calculated start of week and the fixed date)
       $weeklyLeaders = $baseQuery($startOfWeek, $endDateForRanges)->limit(5)->get();

       // 3. Monthly Leaders (Using the calculated start of month and the fixed date)
       $monthlyLeaders = $baseQuery($startOfMonth, $endDateForRanges)->limit(5)->get();

        // dd($dailyLeaders);
        // --- Pass Data to View ---
        return view('pages.leaderboard.index', [ // Assuming your view is resources/views/dashboard/leaderboard.blade.php
            'dailyLeaders' => $dailyLeaders,
            'weeklyLeaders' => $weeklyLeaders,
            'monthlyLeaders' => $monthlyLeaders,
            // Add any other data your view needs
        ]);

        return view('pages.leaderboard.index', get_defined_vars());
    }
}
