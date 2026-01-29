<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index() 
    {
        $user = Auth::user()->load(['subscription.package', 'campaigns']);
        $sub = $user->subscription;

        // Fetch Chart Data: Emails sent last 7 days
        $chartData = Campaign::where('user_id', $user->id)
            ->whereIn('status', ['sending', 'completed'])
            ->where('updated_at', '>=', now()->subDays(7))
            ->select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('SUM(sent) as total_sent')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        //Fetch Recent Campaigns
        $recentCampaigns = $user->campaigns()->latest()->take(5)->get();

        return view('user.dashboard', [
            'user'             => $user,
            'activePackage'    => $sub->package->name ?? 'No Plan',
            'emailsRemaining'  => $user->emailsRemaining(),
            'leadsRemaining'   => $user->leadsRemaining(),
            'runningCampaigns' => $user->campaigns()->whereIn('status', ['queued', 'sending'])->count(),
            'recentCampaigns'  => $recentCampaigns,
            'chartData'        => $chartData,
        ]);
    }
}