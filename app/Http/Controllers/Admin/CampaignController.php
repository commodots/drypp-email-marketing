<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\SmtpServer;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
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
        // Only allow assigning SMTP to queued campaigns
        if ($campaign->status !== 'queued') {
            return back()->withErrors(['msg' => 'Can only assign SMTP to queued campaigns.']);
        }

        $request->validate(['smtp_id' => 'required|exists:smtp_servers,id']);

        $campaign->update([
            'smtp_id' => $request->smtp_id,
            'status' => 'sending'
        ]);

        // Reset failed messages so they can be retried
        $campaign->messages()->where('status', 'failed')->update([
            'status' => 'pending'
        ]);

        return back()->with('success', 'Campaign assigned to SMTP and started.');
    }

    public function toggleStatus(Campaign $campaign)
    {
        // Only allow toggling between paused and sending states
        if (!in_array($campaign->status, ['sending', 'paused'])) {
            return back()->withErrors(['msg' => 'Can only pause/resume campaigns that are sending or paused.']);
        }

        $newStatus = $campaign->status === 'paused' ? 'sending' : 'paused';
        $campaign->update(['status' => $newStatus]);
        return back()->with('success', "Campaign {$newStatus}.");
    }
}