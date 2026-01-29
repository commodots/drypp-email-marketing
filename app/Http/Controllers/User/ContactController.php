<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Models\ContactGroup;
use App\Models\ContactGroupItem;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::where('user_id', auth()->id())->latest()->get();
        $groups = ContactGroup::where('user_id', auth()->id())->get();
        return view('user.contacts.index', compact('contacts', 'groups'));
    }

    public function store(Request $r)
    {
        $r->validate(['email' => 'required|email']);

        $contact = Contact::create([
            'user_id' => auth()->id(),
            'email' => $r->email
        ]);

        if ($r->filled('group_id')) {
            ContactGroupItem::create([
                'contact_id' => $contact->id,
                'contact_group_id' => $r->group_id
            ]);
        }

        return back();
    }
}
