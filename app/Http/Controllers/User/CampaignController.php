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
        $query = Auth::user()->campaigns()->with('emailContent')->withCount([
            'messages as opened_count' => fn($q) => $q->whereNotNull('opened_at'),
            'messages as clicked_count' => fn($q) => $q->whereNotNull('clicked_at')
        ])->latest();

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $campaigns = $query->get();

        foreach ($campaigns as $c) {
            $c->progress = $c->total_emails > 0 ? round(($c->sent / $c->total_emails) * 100) : 0;
            $c->open_rate = $c->total_emails > 0 ? round(($c->opened_count / $c->total_emails) * 100, 1) : 0;
            $c->click_rate = $c->total_emails > 0 ? round(($c->clicked_count / $c->total_emails) * 100, 1) : 0;
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
        if ($campaign->user_id !== Auth::id()) {
            abort(403);
        }

        $campaign->load(['emailContent', 'messages']);

        $previewRecipients = collect();

        if ($campaign->status === 'draft') {

            // Dynamically query based on the saved rules
            if ($campaign->recipient_type === 'group') {
                $previewRecipients = ContactGroupItem::where('contact_group_id', $campaign->group_id)
                    ->join('contacts', 'contacts.id', '=', 'contact_group_items.contact_id')
                    ->select('contacts.email')
                    ->get();
            } elseif ($campaign->recipient_type === 'all') {
                $previewRecipients = Contact::where('user_id', Auth::id())
                    ->select('email')
                    ->get();
            } elseif ($campaign->recipient_type === 'except') {
                $excludedIds = $campaign->excluded_contact_ids ?? [];

                $previewRecipients = Contact::where('user_id', Auth::id())
                    ->whereNotIn('id', $excludedIds)
                    ->select('email')
                    ->get();
            }

            // Map the dynamic contacts to look exactly like the CampaignMessage objects the Blade file expects
            $previewRecipients = $previewRecipients->map(function ($contact) {
                return (object)[
                    'email' => $contact->email,
                    'status' => 'draft_pending' // Custom status just for this view
                ];
            });

            $totalRecipients = $previewRecipients->count();
        } else {
            // For queued/sending/completed campaigns, just use the real messages table
            $previewRecipients = $campaign->messages;
            $totalRecipients = $campaign->total_emails;
        }

        // Calculate Analytics for the detail view
        $stats = [
            'opened' => $campaign->messages->whereNotNull('opened_at')->count(),
            'clicked' => $campaign->messages->whereNotNull('clicked_at')->count(),
            'open_rate' => $totalRecipients > 0 ? round(($campaign->messages->whereNotNull('opened_at')->count() / $totalRecipients) * 100, 2) : 0,
            'click_rate' => $totalRecipients > 0 ? round(($campaign->messages->whereNotNull('clicked_at')->count() / $totalRecipients) * 100, 2) : 0,
        ];

        return view('user.campaigns.show', compact('campaign', 'previewRecipients', 'totalRecipients', 'stats'));
    }

    public function stepTwo(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'sender_email' => 'required|email|max:255',
            'recipient_type' => 'required|in:group,all,except',
            'group_id' => 'nullable|required_if:recipient_type,group',
            'excluded_contact_ids' => 'nullable|array',
            'body' => 'nullable|string', //Catch the email body when going backward
            'format' => 'nullable|in:html,text', // Catch the format when going backward
        ]);

        $defaultTemplate = "<!DOCTYPE html>\n<html>\n<body>\n<h1>Hello {{ name }},</h1>\n<p>Start typing your message here...</p>\n</body>\n</html>";

        session()->flash('show_preview', true);

        //If returning from Step 3, load the existing body. Otherwise, use default.
        session()->flash('preview_body', $request->body ?? $defaultTemplate);
        session()->flash('preview_format', $request->format ?? 'html');

        $metaKeys = $this->extractMetaKeys($request);

        return view('user.campaigns.create_step_two', compact('data', 'defaultTemplate', 'metaKeys'));
    }

    public function stepThree(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'subject' => 'required|string',
            'sender_email' => 'required|email',
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
            // Validate group belongs to user if specified
            $group = ContactGroup::where('user_id', \Illuminate\Support\Facades\Auth::id())
                ->find($request->group_id);
            if (!$group) abort(403, 'Group not found');

            // Filter by Group
            $contactsQuery->whereHas('groups', function ($q) use ($request) {
                $q->where('contact_groups.id', $request->group_id);
            });

            $groupName = $group->name;
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
            'sender_email' => 'required|email|max:255',
            'body' => 'nullable|string',
            'format' => 'required|in:html,text',
            'recipient_type' => 'required|in:group,all,except',
            'group_id' => 'nullable',
            'excluded_contact_ids' => 'nullable|array',
            'action' => 'required|in:draft,send,preview,back,review',
        ]);

        if ($request->action === 'back') {
            return redirect()->route('campaigns.create')->withInput();
        }

        if ($request->action === 'preview') {
            $data = $request->all();
            $defaultTemplate = "<!DOCTYPE html>\n<html>\n<body>\n<h1>Hello {{ name }},</h1>\n<p>Start typing your message here...</p>\n</body>\n</html>";

            session()->flash('show_preview', true);
            session()->flash('preview_body', $request->body);
            session()->flash('preview_format', $request->format);

            $metaKeys = $this->extractMetaKeys($request);

            return view('user.campaigns.create_step_two', compact('data', 'defaultTemplate', 'metaKeys'));
        }

        $isDraft = $request->input('action') === 'draft';
        $status = $isDraft ? 'draft' : 'queued';

        $emails = collect();

        // Gather emails early if not a draft to check quota accurately
        if (!$isDraft) {
            if ($request->recipient_type === 'group') {
                $emails = ContactGroupItem::where('contact_group_id', $request->group_id)
                    ->join('contacts', 'contacts.id', '=', 'contact_group_items.contact_id')
                    ->pluck('contacts.email');
            } elseif ($request->recipient_type === 'all') {
                $emails = Contact::where('user_id', Auth::id())->pluck('email');
            } elseif ($request->recipient_type === 'except') {
                $emails = Contact::where('user_id', Auth::id())
                    ->whereNotIn('id', $request->excluded_contact_ids ?? [])
                    ->pluck('email');
            }

            if ($emails->isEmpty()) {
                return redirect()->route('campaigns.create')->withErrors(['msg' => 'No recipients found.']);
            }

            if (!Auth::user()->hasQuota($emails->count())) {
                return redirect()->route('campaigns.create')->withErrors(['msg' => 'You do not have enough email quota.']);
            }
        }

        // Save everything to DB, including the new rules
        $campaign = Auth::user()->campaigns()->create([
            'name' => $request->name,
            'status' => $status,
            'sender_email' => $request->sender_email,
            'type' => 'bulk', // Defaulting to bulk, adjust if your UI provides this
            // We save these so we know who it was for if we edit the draft later
            'recipient_type' => $request->recipient_type,
            'group_id' => $request->group_id,
            'excluded_contact_ids' => $request->excluded_contact_ids,
        ]);

        $campaign->emailContent()->create([
            'subject' => $request->subject,
            'body' => $request->body,
            'format' => $request->format,
        ]);

        // If it's a draft, stop here! Don't bloat the messages table yet.
        if ($isDraft) {
            return redirect()->route('campaigns.index')
                ->with('success', 'Campaign saved as draft.');
        }

        // If it is NOT a draft, proceed to gather emails and send to queue
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

        //Redirect to Index
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
            'sender_email' => 'required|email|max:255',
            'body' => 'required|string',
            'format' => 'required|in:html,text',
            'recipient_type' => 'required|in:group,all,except',
            'group_id' => 'nullable|required_if:recipient_type,group'
        ]);

        $campaign->update([
            'name' => $request->name,
            'sender_email' => $request->sender_email,
            'recipient_type' => $request->recipient_type,
            'group_id' => $request->group_id,
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

        // Fetch groups so the user can select/change them in the edit view
        $groups = ContactGroup::where('user_id', Auth::id())->get();

        return view('user.campaigns.edit', compact('campaign', 'groups'));
    }

    public function send(Request $request, Campaign $campaign)
    {
        if ($campaign->user_id !== Auth::id()) abort(403);
        if ($campaign->status !== 'draft') abort(400, 'Only drafts can be sent.');

        $emails = collect();

        // Check the rules saved in the database
        if ($campaign->recipient_type === 'group') {
            // Verify group still exists
            $group = ContactGroup::where('user_id', Auth::id())->find($campaign->group_id);
            if (!$group) {
                return back()->withErrors(['msg' => 'Selected group no longer exists.']);
            }

            $emails = ContactGroupItem::where('contact_group_id', $campaign->group_id)
                ->join('contacts', 'contacts.id', '=', 'contact_group_items.contact_id')
                ->pluck('contacts.email');
        } elseif ($campaign->recipient_type === 'all') {
            $emails = Contact::where('user_id', Auth::id())->pluck('email');
        } elseif ($campaign->recipient_type === 'except') {
            $excludedIds = $campaign->excluded_contact_ids ?? [];
            $emails = Contact::where('user_id', Auth::id())
                ->whereNotIn('id', $excludedIds)
                ->pluck('email');
        }

        if ($emails->isEmpty()) {
            return back()->withErrors(['msg' => 'No recipients found for this selection.']);
        }

        // Queue Emails 
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

    private function extractMetaKeys(Request $request)
    {
        $contactsQuery = Contact::where('user_id', Auth::id());

        if ($request->recipient_type === 'group' && $request->group_id) {
            $contactsQuery->whereHas('groups', function ($q) use ($request) {
                $q->where('contact_groups.id', $request->group_id);
            });
        } elseif ($request->recipient_type === 'except') {
            $contactsQuery->whereNotIn('id', $request->excluded_contact_ids ?? []);
        }

        $recipients = $contactsQuery->get(['meta']);

        $metaKeysArray = [];
        foreach ($recipients as $contact) {
            if (is_array($contact->meta)) {
                foreach (array_keys($contact->meta) as $key) {
                    $metaKeysArray[$key] = true;
                }
            }
        }

        return array_keys($metaKeysArray);
    }
}
