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
        $query->whereHas('groups', function($q) use ($request) {
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
            'group_id' => 'nullable|exists:contact_groups,id'
            ]);

        $contact = Contact::firstOrCreate([
            'user_id' => auth()->id(),
            'email' => $r->email
        ]);

        if ($r->filled('group_id')) {
            ContactGroupItem::firstOrCreate([
                'contact_id' => $contact->id,
                'contact_group_id' => $r->group_id
            ]);
        }

        return back()->with('success', 'Contact added.');
    }
}
