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
        // dd(now());
        $total_customers = Customer::count();
        $total_deals = Customer::sum('leads');


        // Current Total Deals for the current day
        $current_day_deals = Customer::whereDate('date', Carbon::today())->sum('leads');

        // Weekly Total of Deals for current week
        $start_of_week = Carbon::now()->startOfWeek();
        $end_of_week = Carbon::now()->endOfWeek();
        $weekly_deals = Customer::whereBetween('date', [$start_of_week, $end_of_week])->sum('leads');

        // Monthly Total of Deals for month
        $start_of_month = Carbon::now()->startOfMonth();
        $end_of_month = Carbon::now()->endOfMonth();
        $monthly_deals = Customer::whereBetween('date', [$start_of_month, $end_of_month])->sum('leads');


        $active = Customer::where('status', 'Active')->sum('leads');
        $cancelled = Customer::where('status', 'CANCELLED')->sum('leads');
        $aor_switched = Customer::where('status', 'AOR SWITCH')->sum('leads');

        $carrier_to_carrier = Customer::where('status', 'CARRIER TO CARRIER')->sum('leads');
        $existing_customer = Customer::where('status', 'EXISTING CUSTOMER')->sum('leads');
        $enrollment_issues = Customer::where('status', 'ENROLLMENT ISSUE')->sum('leads');

        $unpaid = Customer::where('status', 'UNPAID')->sum('leads');
        $prospect = Customer::where('status', 'PROSPECT')->sum('leads');
        $clock_exp_notice = Customer::where('status', 'CLOCK EXPIRATION')->sum('leads');

        $response = [
            'total_users' => $total_customers,

            'total_deals' => $total_deals,
            'current_day' => $current_day_deals,
            'weekly' => $weekly_deals,
            'monthly' => $monthly_deals,

            'active' => $active,
            'cancelled' => $cancelled,
            'aor_switched' => $aor_switched,

            'carrier_to_carrier' => $carrier_to_carrier,
            'existing_customer' => $existing_customer,
            'enrollment_issues' => $enrollment_issues,

            'unpaid' => $unpaid,
            'prospect' => $prospect,
            'clock_exp_notice' => $clock_exp_notice,

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

        $data = $ModelName::where('date', '>', $startDate)
            ->orderBy('date')
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
        $leadData = $ModelName::where('date', '>=', $startDate)
            ->where('leads', '>', 0) // Consider only rows with leads > 0
            ->orderBy('date')
            ->get();

        $leadCount = array_fill_keys($dateRange, 0);


        // Count the records for each date for leads
        $groupedLeadData = $leadData->groupBy(function ($item) {
            return $item->date->format('Y-m-d');
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
