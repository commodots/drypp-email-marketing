<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Models\ContactGroup;
use App\Models\Sequence;
use App\Jobs\SendSequenceEmail;
use App\Models\ContactGroupItem;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $groups = ContactGroup::where('user_id', auth()->id())->get();
        $query = Contact::where('user_id', auth()->id())->with('groups');

        if ($request->has('group')) {
            $query->whereHas('groups', function ($q) use ($request) {
                $q->where('contact_group_id', $request->group);
            });

            // Paginate group-specific listings to avoid loading too many contacts at once.
            $contacts = $query->latest()->paginate(20)->withQueryString();
        } else {
            $contacts = $query->latest()->get();
        }

        return view('user.contacts.index', compact('contacts', 'groups'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
            'group_id' => 'nullable|exists:contact_groups,id',
            'meta' => 'nullable|array',
        ]);

        // Verify group belongs to authenticated user
        if ($r->filled('group_id')) {
            $groupExists = ContactGroup::where('user_id', auth()->id())
                ->where('id', $r->group_id)
                ->exists();
            if (!$groupExists) return back()->withErrors(['group_id' => 'Invalid group selected.']);
        }

        //Find or Create the contact
        $contact = Contact::firstOrCreate(
            ['user_id' => auth()->id(), 'email' => $r->email],
            ['name' => $r->name]
        );

        //Handle Meta Personalization
        if ($r->filled('meta')) {
            $newMeta = [];
            foreach ($r->meta as $key => $value) {
                if ($value !== null && $value !== '') {
                    // Sanitize the key: "Home City" becomes "home_city"
                    $cleanKey = Str::snake(strtolower(trim($key)));
                    $newMeta[$cleanKey] = trim($value);
                }
            }

            // Merge with existing meta so we don't delete old data
            $existingMeta = $contact->meta ?? [];
            $mergedMeta = array_merge($existingMeta, $newMeta);

            $contact->meta = !empty($mergedMeta) ? $mergedMeta : null;
        }

        if ($r->filled('name')) {
            $contact->name = $r->name;
        }

        $contact->save();

        //Handle Group Assignment
        if ($r->filled('group_id')) {
            ContactGroupItem::firstOrCreate([
                'contact_id' => $contact->id,
                'contact_group_id' => $r->group_id
            ]);

            // Trigger Automation
            $sequences = Sequence::where('group_id', $r->group_id)->with('steps')->get();
            foreach ($sequences as $sequence) {
                foreach ($sequence->steps as $step) {
                    dispatch(new SendSequenceEmail($contact, $step))
                        ->delay(now()->addDays($step->delay_days));
                }
            }
        }

        return back()->with('success', 'Contact added successfully.');
    }

    public function import(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:csv,txt|max:10240',
        'group_id' => [
            'nullable',
            Rule::exists('contact_groups', 'id')->where(function ($query) {
                $query->where('user_id', auth()->id());
            }),
        ],
    ]);

    // Save file to a temporary location
    $path = $request->file('file')->store('temp');
    $fullPath = storage_path('app/' . $path);

    // Dispatch the job
    \App\Jobs\ProcessContactImport::dispatch($fullPath, auth()->id(), $request->group_id);

    return back()->with('success', 'Import started! Your contacts will appear shortly.');
}


    public function destroy(Contact $contact)
    {
        if ($contact->user_id !== auth()->id()) abort(403);
        $contact->delete();
        return back()->with('success', 'Contact deleted.');
    }
}
