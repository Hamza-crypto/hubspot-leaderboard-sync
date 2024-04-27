<?php

namespace App\Http\Controllers;

use App\Models\Leaderboard;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function index()
    {
        $agents = Leaderboard::toBase()->where('tab', 'No Cost ACA')->latest('leads')->get();
        return view('pages.leaderboard.index', get_defined_vars());
    }
}