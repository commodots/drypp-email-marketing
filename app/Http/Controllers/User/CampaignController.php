<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\Contact;
use App\Models\ContactGroupItem;
use App\Models\ContactGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->campaigns()->with('emailContent')->latest();

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $campaigns = $query->get();


        foreach ($campaigns as $c) {
            $c->progress = $c->total_emails > 0 ? round(($c->sent / $c->total_emails) * 100) : 0;
        }
        return view('user.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $groups = ContactGroup::where('user_id', Auth::id())->get();
        $contacts = Contact::where('user_id', Auth::id())->get();

        return view('user.campaigns.create', compact('groups', 'contacts'));
    }

    public function show(Campaign $campaign)
    {
        if ($campaign->user_id !== Auth::id()) abort(403);
        $campaign->load(['emailContent', 'messages']);
        return view('user.campaigns.show', compact('campaign'));
    }

    public function stepTwo(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',

            'recipient_type' => 'required|in:group,all,except,',

            'group_id' => 'nullable|required_if:recipient_type,group',
            'excluded_contact_ids' => 'nullable|array',
        ]);

        $defaultTemplate = "<!DOCTYPE html>\n<html>\n<body>\n<h1>Hello @{{ \$name }},</h1>\n<p>Start typing your message here...</p>\n</body>\n</html>";

        session()->flash('show_preview', true);
        session()->flash('preview_body', $defaultTemplate);
        session()->flash('preview_format', 'html');

        
        return view('user.campaigns.create_step_two', compact('data', 'defaultTemplate'));
    }

    public function stepThree(Request $request)
{
    // 1. Validate Step 2 Data
    $data = $request->validate([
        'name' => 'required|string',
        'subject' => 'required|string',
        'body' => 'nullable|string',
        'format' => 'required|in:html,text',
        'recipient_type' => 'required|in:group,all,except',
        'group_id' => 'nullable',
        'excluded_contact_ids' => 'nullable|array',
        'action' => 'required|in:draft,send,preview,back,review', 
    ]);

    if ($request->action === 'back') return redirect()->route('campaigns.create')->withInput();
    if ($request->action === 'draft') return $this->store($request); 
    if ($request->action === 'preview') return $this->store($request); 

    $contactsQuery = Contact::query()->where('user_id', \Illuminate\Support\Facades\Auth::id());

    if ($request->recipient_type === 'group') {
        // Filter by Group
        $contactsQuery->whereHas('groups', function($q) use ($request) {
            $q->where('contact_groups.id', $request->group_id);
        });
        
        // Fetch Group Name for display
        $groupName = ContactGroup::find($request->group_id)->name;

    } elseif ($request->recipient_type === 'except') {
        // Filter by Exception
        $contactsQuery->whereNotIn('id', $request->excluded_contact_ids ?? []);
        $groupName = "All Contacts (Except " . count($request->excluded_contact_ids ?? []) . ")";

    } else {
        // All Contacts
        $groupName = "All Contacts";
    }

    // Get the actual list 
    $recipients = $contactsQuery->select('email', 'id', 'created_at')->paginate(50);
    $totalCount = $contactsQuery->count();

    return view('user.campaigns.create_step_three', compact('data', 'recipients', 'totalCount', 'groupName'));
}

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'nullable|string',
            'format' => 'required|in:html,text',
            'recipient_type' => 'required|in:group,all,except',
            'group_id' => 'nullable',
            'excluded_contact_ids' => 'nullable|array',
            'action' => 'required|in:draft,send,preview,back',
        ]);

        if ($request->action === 'back') {
            // This sends the user back to Step 1, but "carries" the data 
            // they already typed (Name, Subject, etc.) so they don't have to re-type it.
            return redirect()->route('campaigns.create')->withInput();
        }

        if ($request->action === 'preview') {
            $data = $request->all();

            $defaultTemplate = "<!DOCTYPE html>\n<html>\n<body>\n<h1>Hello @{{ \$name }},</h1>\n<p>Start typing your message here...</p>\n</body>\n</html>";

            session()->flash('show_preview', true);
            session()->flash('preview_body', $request->body);
            session()->flash('preview_format', $request->format);

            return view('user.campaigns.create_step_two', compact('data', 'defaultTemplate'));
        }

        $isDraft = $request->input('action') === 'draft';
        $status = $isDraft ? 'draft' : 'queued';

        // 1. Save to DB immediately (Safe Draft)
        $campaign = Auth::user()->campaigns()->create([
            'name' => $request->name,
            'format' => $request->format,
            'status' => $status,
            // We save these so we know who it was for if we edit the draft later
            'recipient_type' => $request->recipient_type,
            'group_id' => $request->group_id,
        ]);

        $campaign->emailContent()->create([
            'subject' => $request->subject,
            'body' => $request->body,
            'format' => $request->format,
        ]);

        if ($isDraft) {
            return redirect()->route('campaigns.index')
                ->with('success', 'Campaign saved as draft.');
        }

        $emails = collect();

        if ($request->recipient_type === 'group') {
            $emails = ContactGroupItem::where('contact_group_id', $request->group_id)
                ->join('contacts', 'contacts.id', '=', 'contact_group_items.contact_id')
                ->pluck('contacts.email');
        } elseif ($request->recipient_type === 'all') {
            // Case B: All Contacts
            $emails = Contact::where('user_id', Auth::id())
                ->pluck('email');
        } elseif ($request->recipient_type === 'except') {
            // Case C: All Except...
            $emails = Contact::where('user_id', Auth::id())
                ->whereNotIn('id', $request->excluded_contact_ids ?? [])
                ->pluck('email');
        }


        if ($emails->isEmpty()) {
            // Clean up if no emails were found
            $campaign->delete();
            return redirect()->route('campaigns.create')
                ->withErrors(['msg' => 'No recipients found. Campaign was cancelled.']);
        }

        $messages = [];


        foreach ($emails as $email) {
            $messages[] = [
                'campaign_id' => $campaign->id,
                'email' => $email,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Use array_chunk to prevent database limits if list is huge (e.g. 5000+)
        foreach (array_chunk($messages, 1000) as $chunk) {
            CampaignMessage::insert($chunk);
        }

        // Update the total count on the campaign
        $campaign->update(['total_emails' => count($messages)]);

        // 6. Redirect to Index
        return redirect()->route('campaigns.index')
            ->with('success', 'Campaign created and ' . count($messages) . ' emails queued successfully!');
    }

    public function update(Request $request, Campaign $campaign)
    {
        if ($campaign->user_id !== Auth::id()) abort(403);
        if ($campaign->status !== 'draft') return back()->withErrors(['msg' => 'Only drafts can be edited.']);

        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'format' => 'required|in:html,text'
        ]);

        $campaign->update([
            'name' => $request->name,
            'format' => $request->format,
        ]);

        $campaign->emailContent()->update([
            'subject' => $request->subject,
            'body' => $request->body,
            'format' => $request->format,
        ]);

        return redirect()->route('campaigns.index')->with('success', 'Draft updated.');
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


        $emails = collect();

        if ($campaign->recipient_type === 'group') {
            $emails = ContactGroupItem::where('contact_group_id', $campaign->group_id)
                ->join('contacts', 'contacts.id', '=', 'contact_group_items.contact_id')
                ->pluck('contacts.email');
        } elseif ($campaign->recipient_type === 'all') {
            $emails = Contact::where('user_id', Auth::id())->pluck('email');
        }

        if ($emails->isEmpty()) {
            return back()->withErrors(['msg' => 'No recipients found for this selection.']);
        }

        // 3. Queue Emails 
        $messages = [];
        foreach ($emails as $email) {
            $messages[] = [
                'campaign_id' => $campaign->id,
                'email' => $email,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($messages, 1000) as $chunk) {
            CampaignMessage::insert($chunk);
        }

        $campaign->update([
            'status' => 'queued',
            'total_emails' => count($messages),
        ]);

        return redirect()->route('campaigns.index')->with('success', count($messages) . ' emails queued!');
    }

    public function destroy(Campaign $campaign)
    {
        if ($campaign->user_id !== Auth::id() || $campaign->status !== 'draft') abort(403);
        $campaign->delete();
        return redirect()->route('campaigns.index')->with('success', 'Draft deleted.');
    }
}
