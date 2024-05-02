<?php

namespace App\Http\Controllers;

use App\Models\Leaderboard;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    public function index()
    {

        $board_name = 'NCA';
        $agents = Leaderboard::toBase()->where('tab', 'No Cost ACA')->whereDate('updated_at', Carbon::today())->latest('leads')->get();
        return view('pages.leaderboard.index', get_defined_vars());
    }

    public function leader_spanish()
    {
        $board_name = 'SPANISH';
        $agents = Leaderboard::toBase()->where('tab', 'Spanish')->whereDate('updated_at', Carbon::today())->latest('leads')->get();
        return view('pages.leaderboard.index', get_defined_vars());
    }
}
