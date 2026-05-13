<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $campaigns = Auth::user()->campaigns()
            ->whereIn('status', ['sending', 'completed'])
            ->latest()
            ->get();

        foreach ($campaigns as $c) {
            $total = max($c->total_emails, 1);
            $c->open_rate = round(($c->messages()->whereNotNull('opened_at')->count() / $total) * 100, 1);
            $c->click_rate = round(($c->messages()->whereNotNull('clicked_at')->count() / $total) * 100, 1);
        }

        return view('user.reports', compact('campaigns'));
    }

    public function toggleAutoReport(Request $request)
    {
        $user = Auth::user();
        $user->update([
            'auto_reports' => $request->has('enabled')
        ]);

        return back()->with('success', 'Report preferences updated.');
    }

    public function export(Campaign $campaign)
    {
        if ($campaign->user_id !== Auth::id()) abort(403);

        $headers = ["Campaign Name", "Status", "Total Emails", "Sent", "Opened", "Clicked", "Open Rate %", "Click Rate %", "Created At"];

        $callback = function() use ($campaign, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            
            $total = max($campaign->total_emails, 1);
            fputcsv($file, [
                $campaign->name,
                $campaign->status,
                $campaign->total_emails,
                $campaign->sent,
                $campaign->messages()->whereNotNull('opened_at')->count(),
                $campaign->messages()->whereNotNull('clicked_at')->count(),
                round(($campaign->messages()->whereNotNull('opened_at')->count() / $total) * 100, 2),
                round(($campaign->messages()->whereNotNull('clicked_at')->count() / $total) * 100, 2),
                $campaign->updated_at
            ]);
            fclose($file);
        };

        return response()->streamDownload($callback, "report-campaign-{$campaign->id}.csv");
    }
}
