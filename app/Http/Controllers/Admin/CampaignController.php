<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\SmtpServer;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
public function index(Request $request)    {
        //Get Campaigns waiting for assignment (Queued)
        $status = $request->get('status', 'queued');

        $query = Campaign::with(['user', 'smtp'])
        ->latest();

     if ($status !== 'all') {
        $query->where('status', $status);
    }

    $campaigns = $query->get();

    $smtps = SmtpServer::where('active', true)->get(); 

        return view('admin.campaigns.index', compact('campaigns', 'smtps', 'status'));
    }

    public function assignSmtp(Request $request, Campaign $campaign)
    {
        $request->validate(['smtp_id' => 'required|exists:smtp_servers,id']);

        $campaign->update([
            'smtp_id' => $request->smtp_id,
            'status' => 'sending'
        ]);

        $campaign->messages()->where('status', 'failed')->update([
        'status' => 'pending'
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