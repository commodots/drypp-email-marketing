<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Models\ContactGroup;
use App\Models\ContactGroupItem;

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
        }

        $contacts = $query->latest()->get();

        return view('user.contacts.index', compact('contacts', 'groups'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
            'group_id' => 'nullable|exists:contact_groups,id',
            'meta' => 'nullable|array',
            'meta.*' => 'nullable|string|max:500',
        ]);

        // Verify group belongs to authenticated user
        if ($r->filled('group_id')) {
            $groupExists = ContactGroup::where('user_id', auth()->id())
                ->where('id', $r->group_id)
                ->exists();
            if (!$groupExists) {
                return back()->withErrors(['group_id' => 'Invalid group selected.']);
            }
        }

        // Prepare meta data - filter out empty values
        $meta = null;
        if ($r->filled('meta')) {
            $meta = array_filter($r->meta, function ($value) {
                return $value !== null && $value !== '';
            });
            $meta = !empty($meta) ? $meta : null;
        }

        $contact = Contact::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'email' => $r->email
            ],
            [
                'name' => $r->name,
                'meta' => $meta,
            ]
        );

        // Update existing contact with new meta if it already existed
        if ($r->filled('meta') || $r->filled('name')) {
            $contact->update([
                'name' => $r->name ?? $contact->name,
                'meta' => $meta ?? $contact->meta,
            ]);
        }

        if ($r->filled('group_id')) {
            ContactGroupItem::firstOrCreate([
                'contact_id' => $contact->id,
                'contact_group_id' => $r->group_id
            ]);
        }

        return back()->with('success', 'Contact added.');
    }
    public function import(Request $request)
    {
        // Validate the file upload
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120', // Max 5MB
        ]);

        $file = $request->file('file');

        // Open the file for reading
        $handle = fopen($file->path(), 'r');

        // Read the first row to get the column headers
        $headers = fgetcsv($handle);
        if (!$headers) {
            return back()->withErrors(['file' => 'The uploaded file is empty or invalid.']);
        }

        // Clean up headers (lowercase, remove spaces) to make matching easier
        $headers = array_map(function ($header) {
            return strtolower(trim($header));
        }, $headers);

        // Find the index of the email and name columns
        $emailIndex = array_search('email', $headers);
        $nameIndex = array_search('name', $headers);

        if ($emailIndex === false) {
            return back()->withErrors(['file' => 'Your CSV must contain an "email" column header.']);
        }

        $importedCount = 0;
        $importedIds = [];

        // Loop through the remaining rows in the CSV
        while (($row = fgetcsv($handle)) !== false) {
            $email = isset($row[$emailIndex]) ? trim($row[$emailIndex]) : null;

            // Skip rows with no email or invalid emails
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $name = ($nameIndex !== false && isset($row[$nameIndex])) ? trim($row[$nameIndex]) : null;

            // Gather any extra columns to store in the 'meta' JSON field
            $meta = [];
            foreach ($headers as $index => $header) {
                // Ignore the email and name columns, only grab the extra stuff
                if ($index !== $emailIndex && $index !== $nameIndex && isset($row[$index])) {
                    $value = trim($row[$index]);
                    if ($value !== '') {
                        $meta[$header] = $value;
                    }
                }
            }

            
            $contact = Contact::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'email' => $email
                ],
                [
                    'name' => $name,
                    // If meta has items, save it, otherwise save null
                    'meta' => !empty($meta) ? $meta : null
                ]
            );
            $importedIds[] = $contact->id;
            $importedCount++;
        }

        fclose($handle);

        
        return back()
                ->with('success', "Successfully imported {$importedCount} contacts.")
                ->with('imported_ids', $importedIds);
    }

    public function destroy(Contact $contact)
    {
        if ($contact->user_id !== auth()->id()) {
            abort(403);
        }

        $contact->delete();
        return back()->with('success', 'Contact deleted.');
    }
}
