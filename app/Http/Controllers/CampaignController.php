<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::where('user_id', Auth::id())->latest()->get();
        return view('campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('campaigns.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->package) {
            return back()->withErrors(['msg' => 'You must purchase a package first.']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cold_call,email_marketing',
            'email_subject' => 'nullable|string|max:255',
            'email_body' => 'nullable|string',
        ]);

        
        $user->campaigns()->create([
            'name' => $request->name,
            'type' => $request->type,
            'email_subject' => $request->email_subject,
            'email_body' => $request->email_body,
            'status' => 'draft',
            'emails_count' => 0
        ]);

        return redirect()->route('campaigns.index')->with('success', 'Campaign saved as draft.');
    }

    public function send(Campaign $campaign)
    {
        if ($campaign->user_id !== Auth::id()) {
            abort(403);
        }

        if ($campaign->status !== 'draft') {
            return back()->withErrors(['error' => 'Campaign is already sent.']);
        }

        $user = Auth::user();

        $simulatedCount = rand(50, 500);

        if ($user->emailsRemaining() < $simulatedCount) {
            return back()->withErrors(['error' => 'Insufficient email quota.']);
        }

        $campaign->update([
            'status' => 'sent',
            'emails_count' => $simulatedCount,
        ]);

        $user->increment('emails_used', $simulatedCount);

        return back()->with('success', 'Campaign sent successfully!');
    }
}
