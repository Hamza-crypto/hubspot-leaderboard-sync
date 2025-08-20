<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $today = Carbon::today();

        $startOfWeek = $now->copy()->startOfWeek(Carbon::MONDAY);
        $startOfMonth = $now->copy()->startOfMonth();

        $baseQuery = function ($startDate, $endDate) {
            return DB::table('customers')
                ->select('agent', DB::raw('COUNT(*) as customer_count'))
                ->whereNotNull('agent')
                ->where('agent', '!=', '')
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('agent')
                ->orderByDesc('customer_count');
        };

        $dailyLeaders = DB::table('customers')
            ->select('agent', DB::raw('COUNT(*) as customer_count'))
            ->whereNotNull('agent')
            ->where('agent', '!=', '')
            ->whereDate('date', $today)
            ->groupBy('agent')
            ->orderByDesc('customer_count')
            ->limit(500)
            ->get();

        $weeklyLeaders = $baseQuery($startOfWeek, $now)->limit(6)->get();
        $monthlyLeaders = $baseQuery($startOfMonth, $now)->limit(6)->get();
        $today_sales = Customer::whereBetween('updated_at', [$today, $now])->count();

        return view('pages.leaderboard.index', [
            'dailyLeaders' => $dailyLeaders,
            'weeklyLeaders' => $weeklyLeaders,
            'monthlyLeaders' => $monthlyLeaders,
            'today_total_deals' => $today_sales,
        ]);
    }
}
