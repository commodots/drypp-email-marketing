<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\SmtpServer;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        //Get Campaigns waiting for assignment (Queued)
        $queuedCampaigns = Campaign::with('user')
            ->where('status', 'queued')
            ->latest()
            ->get();

        //Get Campaigns that are running or done (For monitoring)
        $activeCampaigns = Campaign::with(['user', 'smtp'])
            ->whereIn('status', ['sending', 'completed', 'paused'])
            ->latest()
            ->get();

        // Get Active SMTPs for the dropdown
        $smtps = SmtpServer::where('active', true)->get();

        return view('admin.campaigns.index', compact('queuedCampaigns', 'activeCampaigns', 'smtps'));
    }

    public function assignSmtp(Request $request, Campaign $campaign)
    {
        $request->validate(['smtp_id' => 'required|exists:smtp_servers,id']);

        $campaign->update([
            'smtp_id' => $request->smtp_id,
            'status' => 'sending'
        ]);

        return back()->with('success', 'Campaign assigned to SMTP and started.');
    }
    public function toggleStatus(Campaign $campaign)
    {
        $newStatus = $campaign->status === 'paused' ? 'sending' : 'paused';
        $campaign->update(['status' => $newStatus]);
        return back()->with('success', "Campaign {$newStatus}");
    }
}