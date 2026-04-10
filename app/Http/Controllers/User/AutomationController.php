<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Sequence;
use App\Models\SequenceStep;
use App\Models\ContactGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutomationController extends Controller
{
    public function index()
    {
        $sequences = Sequence::where('user_id', Auth::id())->withCount('steps')->get();
        $groups = ContactGroup::where('user_id', Auth::id())->get();
        return view('user.automation.index', compact('sequences', 'groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'group_id' => 'required|exists:contact_groups,id'
        ]);

        Sequence::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'group_id' => $request->group_id
        ]);

        return back()->with('success', 'Sequence created. Now add some steps!');
    }

    public function show(Sequence $automation)
    {
        if ($automation->user_id !== Auth::id()) abort(403);
        $automation->load('steps');
        return view('user.automation.show', compact('automation'));
    }

    public function addStep(Request $request, Sequence $automation)
    {
        if ($automation->user_id !== Auth::id()) abort(403);

        $request->validate([
            'delay_days' => 'required|integer|min:0',
            'subject' => 'required|string|max:255',
            'body' => 'required|string'
        ]);

        $automation->steps()->create($request->only('delay_days', 'subject', 'body'));

        return back()->with('success', 'Step added to sequence.');
    }

    public function updateStep(Request $request, SequenceStep $step)
    {
        if ($step->sequence->user_id !== Auth::id()) abort(403);

        $request->validate([
            'delay_days' => 'required|integer|min:0',
            'subject' => 'required|string|max:255',
            'body' => 'required|string'
        ]);

        $step->update($request->only('delay_days', 'subject', 'body'));
        return back()->with('success', 'Step updated.');
    }

    public function destroy(Sequence $automation)
    {
        if ($automation->user_id !== Auth::id()) abort(403);
        $automation->delete();
        return redirect()->route('automation.index')->with('success', 'Sequence deleted.');
    }

    public function destroyStep(SequenceStep $step)
    {
        $step->delete();
        return back()->with('success', 'Step removed.');
    }
}
