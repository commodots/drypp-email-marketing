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

        $headers = ["Recipient", "Status", "Opened", "Clicked", "Sent At"];

        $callback = function() use ($campaign, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, [
                $campaign->name,
                $campaign->status,
                $campaign->opens,
                $campaign->clicks,
                $campaign->updated_at
            ]);
            fclose($file);
        };

        return response()->streamDownload($callback, "report-campaign-{$campaign->id}.csv");
    }
}