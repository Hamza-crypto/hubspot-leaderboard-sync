<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $startOfToday = Carbon::today();
        $endOfToday = Carbon::now();

        $start_of_week = Carbon::now()->startOfWeek();
        $end_of_week = Carbon::now()->endOfWeek();

        $start_of_month = Carbon::now()->startOfMonth();
        $end_of_month = Carbon::now()->endOfMonth();

        $total_customers = Customer::count();

        $today_sales = Customer::whereBetween('date', [$startOfToday, $endOfToday])->count();
        $weekly_sales = Customer::whereBetween('date', [$start_of_week, $end_of_week])->count();

        $monthly_sales = Customer::whereBetween('date', [$start_of_month, $end_of_month])->count();

        $active = Customer::where('status', 'Active')->count();
        $cancelled = Customer::where('status', 'Cancelled')->count();
        $duplicate = Customer::where('status', 'Duplicate')->count();
        $future_active = Customer::where('status', 'Future Active Policy')->count();
        $no_carrier_match = Customer::where('status', 'No Carrier Match')->count();
        $unknown = Customer::where('status', 'Unknown')->count();
        $denied = Customer::where('status', 'Denied')->count();
        $pending = Customer::where('status', 'Pending')->count();

        $response = [
            'total_customers' => $total_customers,
            'today_sales' => $today_sales,
            'weekly_sales' => $weekly_sales,
            'monthly_sales' => $monthly_sales,
            'active' => $active,
            'cancelled' => $cancelled,
            'duplicate' => $duplicate,
            'future_active_policy' => $future_active,
            'no_carrier_match' => $no_carrier_match,
            'unknown' => $unknown,
            'denied' => $denied,
            'pending' => $pending
        ];

        return response()->json($response);
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

    public function today_deals()
    {
        $current_day_deals = Customer::whereDate('date', Carbon::today())->sum('leads');
        $response = [
            'today_deals_other_hubspot' => $current_day_deals
        ];

        return response()->json($response);
    }
}
