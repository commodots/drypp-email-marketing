<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Models\ContactGroup;
use App\Models\ContactGroupItem;
use Illuminate\Support\Str;

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
        }

        return back()->with('success', 'Contact added successfully.');
    }

    public function import(Request $request)
    {
        // Validate the file upload
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120', // Max 5MB
        ]);

        try {
            $handle = fopen($request->file('file')->path(), 'r');
            if (!$handle) {
                return back()->withErrors(['file' => 'Unable to open CSV file.']);
            }

            $headers = fgetcsv($handle);
            
            if (!$headers) {
                fclose($handle);
                return back()->withErrors(['file' => 'Invalid CSV.']);
            }

            if ($headers) {
        $headers[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $headers[0]);
    }

            // Sanitize Headers for Meta Keys
            $headers = array_map(function ($header) {
                return Str::snake(strtolower(trim($header)));
            }, $headers);

            $emailIndex = array_search('email', $headers);
            $nameIndex = array_search('name', $headers);

            if ($emailIndex === false) {
                fclose($handle);
                return back()->withErrors(['file' => 'Your CSV must contain an "email" column header.']);
            }

            $importedCount = 0;
            $importedIds = [];

            // Loop through the remaining rows in the CSV
            while (($row = fgetcsv($handle)) !== false) {
                $email = isset($row[$emailIndex]) ? trim($row[$emailIndex]) : null;
                // Skip rows with no email or invalid emails
                if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) continue;

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

                // Find or create contact first
                $contact = Contact::firstOrCreate(
                    ['user_id' => auth()->id(), 'email' => $email],
                    ['name' => $name, 'meta' => null]
                );

                // Merge meta data like in store() method to preserve existing data
                if (!empty($meta)) {
                    $existingMeta = $contact->meta ?? [];
                    $mergedMeta = array_merge($existingMeta, $meta);
                    $contact->meta = $mergedMeta;
                }

                // Update name if provided and different
                if ($name && $contact->name !== $name) {
                    $contact->name = $name;
                }

                $contact->save();
                
                $importedIds[] = $contact->id;
                $importedCount++;
            }

            fclose($handle);

            return back()
                    ->with('success', "Successfully imported {$importedCount} contacts.")
                    ->with('imported_ids', $importedIds);
        } catch (\Exception $e) {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            return back()->withErrors(['file' => 'Error processing CSV: ' . $e->getMessage()]);
        }
    }

    public function destroy(Contact $contact)
    {
        if ($contact->user_id !== auth()->id()) abort(403);
        $contact->delete();
        return back()->with('success', 'Contact deleted.');
    }
}