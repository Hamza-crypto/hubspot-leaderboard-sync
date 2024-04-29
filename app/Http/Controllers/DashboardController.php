<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    //For admin dashboard
    public function index()
    {
        $total_customers = Customer::count();
        $total_deals = Customer::sum('leads');

        $today_deals = Customer::whereDate('created_at', Carbon::today())->sum('leads');

        $response = [
            'total_users' => $total_customers,
            'total_deals' => $total_deals,
            'today_deals' => $today_deals,
            'users_chart' => $this->chartData(Customer::class),
            'deals_chart' => $this->dealsChartData(Customer::class),
        ];

        return response()->json($response);


        // $cacheKey = 'dashboard_stats_cache';
        // $cacheDuration = Carbon::now()->addHours(2);
        // $cachedData = Cache::get($cacheKey);

        // if ($cachedData) {
        //     return $cachedData;
        // } else {
        //     /*
        //      * Users count based on role
        //      */
        //     $total_customers = Customer::count();
        //     $total_deals = Customer::sum('leads');


        //     $response = [
        //         'total_users' => $total_customers,
        //         'total_deals' => $total_deals,
        //         'users_chart' => $this->chartData(Customer::class),
        //     ];

        //     Cache::put($cacheKey, $response, $cacheDuration);

        //     return response()->json($response);
        // }
    }

    public function chartData($ModelName)
    {
        $startDate = Carbon::today()->subDays(6);
        $endDate = Carbon::today();

        // Generate an array of dates between the start and end dates
        $dateRange = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateRange[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        $data = $ModelName::where('created_at', '>', $startDate)
            ->orderBy('created_at')
            ->get();

        // Create an associative array with dates as keys and initial count as 0
        $createdCount = array_fill_keys($dateRange, 0);

        // Count the records for each date
        $groupedData = $data->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        })->map(function ($group) {
            return $group->count();
        });

        // Merge the counts into the createdCount array
        foreach ($groupedData as $date => $count) {
            $createdCount[$date] = $count;
        }

        return [
            'labels' => $dateRange,
            'createdData' => $createdCount,
        ];
    }

    public function dealsChartData($ModelName)
    {

        $startDate = Carbon::today()->subDays(6);
        $endDate = Carbon::today();

        // Generate an array of dates between the start and end dates
        $dateRange = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateRange[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        // Get data for leads
        $leadData = $ModelName::where('created_at', '>', $startDate)
            ->where('leads', '>', 0) // Consider only rows with leads > 0
            ->orderBy('created_at')
            ->get();

        $leadCount = array_fill_keys($dateRange, 0);


        // Count the records for each date for leads
        $groupedLeadData = $leadData->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        })->map(function ($group) {
            return $group->sum('leads');
        });


        foreach ($groupedLeadData as $date => $count) {
            $leadCount[$date] = $count;
        }

        return [
            'labels' => $dateRange,
            'createdData' => $leadCount,
        ];

    }
}
