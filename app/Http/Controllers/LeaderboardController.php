<?php

namespace App\Http\Controllers;

use App\Models\Leaderboard;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    public function index()
    {
        $agents = Leaderboard::toBase()->where('tab', 'No Cost ACA')->whereDate('updated_at', Carbon::today())->latest('leads')->get();
        return view('pages.leaderboard.index', get_defined_vars());
    }
}
