<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('package');
        $recentCampaigns = Campaign::where('user_id', $user->id)->latest()->take(5)->get();

        return view('dashboard', [
            'user' => $user,
            'emailsRemaining' => $user->emailsRemaining(),
            'leadsRemaining' => $user->leadsRemaining(),
            'recentCampaigns' => $recentCampaigns
        ]);
    }
}
