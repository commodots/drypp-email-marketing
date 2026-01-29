<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ContactGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactGroupController extends Controller
{
    public function index()
    {
        $groups = ContactGroup::where('user_id', Auth::id())->withCount('contacts')->get();
        return view('user.groups.index', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        ContactGroup::create(['user_id' => Auth::id(), 'name' => $request->name]);
        return back()->with('success', 'Group created.');
    }
    public function destroy(ContactGroup $group)
    {
        if ($group->user_id !== Auth::id()) {
            abort(403);
        }
        
        $group->delete();
        return back()->with('success', 'Group deleted.');
    }
}