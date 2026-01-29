<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\ContactGroupItem;
use App\Models\ContactGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Auth::user()->campaigns()->with('emailContent')->latest()->get();
        $groups = ContactGroup::where('user_id', Auth::id())->get();
        foreach ($campaigns as $c) {
            $c->progress = $c->total_emails > 0 ? round(($c->sent / $c->total_emails) * 100) : 0;
        }
        return view('user.campaigns.index', compact('campaigns', 'groups'));
    }

    public function create()
    {
        $groups = ContactGroup::where('user_id', Auth::id())->get();
        return view('user.campaigns.create', compact('groups'));
    }

    public function show(Campaign $campaign)
    {
        if ($campaign->user_id !== Auth::id()) abort(403);
        $campaign->load('emailContent');
        return view('user.campaigns.show', compact('campaign'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cold,bulk',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'format' => 'required|in:html,text',
            'group_id' => 'required|exists:contact_groups,id'
        ]);

        $campaign = Auth::user()->campaigns()->create([
            'name' => $request->name,
            'type' => $request->type,
            'format' => $request->format,
            'status' => 'draft',
        ]);

        $campaign->emailContent()->create([
            'subject' => $request->subject,
            'body' => $request->body,
            'format' => $request->format,
        ]);

        if ($request->format === 'html') {
            
            session()->flash('campaign_body', $request->body);

            return redirect()->route('campaigns.show', $campaign)
                ->with('success', 'Draft saved! HTML Preview generated below.');
        } else {
           
            return redirect()->route('campaigns.index')
                ->with('success', 'Text campaign saved successfully.');
        }
    }

    public function update(Request $request, Campaign $campaign)
    {
        if ($campaign->user_id !== Auth::id()) abort(403);
        if ($campaign->status !== 'draft') return back()->withErrors(['msg' => 'Only drafts can be edited.']);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cold,bulk',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'format' => 'required|in:html,text'
        ]);

        $campaign->update([
            'name' => $request->name,
            'type' => $request->type,
            'format' => $request->format,
        ]);

        $campaign->emailContent()->update([
            'subject' => $request->subject,
            'body' => $request->body,
            'format' => $request->format,
        ]);

        if ($request->format === 'html') {
            session()->forget('campaign_body');
            session()->flash('campaign_body', $request->body);

            return redirect()->route('campaigns.show', $campaign)
                ->with('success', 'Draft updated! HTML Preview generated below.');
        } else {
            return redirect()->route('campaigns.index')
                ->with('success', 'Text campaign updated successfully.');
        }
    }

    public function edit(Campaign $campaign)
    {
        if ($campaign->user_id !== Auth::id() || $campaign->status !== 'draft') abort(403);
        $campaign->load('emailContent');
        return view('user.campaigns.edit', compact('campaign'));
    }

    public function send(Request $request, Campaign $campaign)
    {
        if ($campaign->user_id !== Auth::id()) abort(403);
        if ($campaign->status !== 'draft') return back()->withErrors(['msg' => 'Campaign is not in draft.']);
        $request->validate(['group_id' => 'required|exists:contact_groups,id']);
        
        $emails = ContactGroupItem::where('contact_group_id', $request->group_id)
            ->join('contacts', 'contacts.id', '=', 'contact_group_items.contact_id')
            ->pluck('contacts.email');

        if ($emails->isEmpty()) return back()->withErrors(['msg' => 'Group is empty.']);

        $messages = $emails->map(fn($email) => [
            'campaign_id' => $campaign->id, 'email' => $email, 'status' => 'pending', 
            'created_at' => now(), 'updated_at' => now()
        ])->toArray();

        CampaignMessage::insert($messages);
        $campaign->update(['status' => 'queued', 'total_emails' => count($messages)]);
        Auth::user()->subscription->increment('emails_used', count($messages));

        return back()->with('success', 'Campaign queued successfully.');
    }

    public function destroy(Campaign $campaign)
    {
        if ($campaign->user_id !== Auth::id() || $campaign->status !== 'draft') abort(403);
        $campaign->delete();
        return redirect()->route('campaigns.index')->with('success', 'Draft deleted.');
    }
}