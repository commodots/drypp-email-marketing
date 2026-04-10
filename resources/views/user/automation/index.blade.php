@extends('layouts.app')
@section('title', 'Email Automations')
@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center bg-white p-6 rounded-xl border shadow-sm">
        <div>
            <h2 class="text-xl font-bold">Sequences</h2>
            <p class="text-sm text-gray-500">Automated emails triggered by group entry</p>
        </div>
        <button onclick="document.getElementById('new-sequence-modal').classList.toggle('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold">
            + New Sequence
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($sequences as $seq)
        <div class="bg-white p-6 rounded-xl border shadow-sm hover:border-blue-300 transition">
            <h3 class="font-bold text-lg mb-1">{{ $seq->name }}</h3>
            <p class="text-xs text-gray-400 uppercase font-black mb-4">Trigger: Group Added</p>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">{{ $seq->steps_count }} Steps</span>
                <a href="{{ route('automation.show', $seq) }}" class="text-blue-600 font-bold text-sm">Edit Steps →</a>
            </div>
        </div>
        @endforeach
    </div>
</div>

<div id="new-sequence-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl p-8 max-w-md w-full">
        <h2 class="text-xl font-bold mb-6">Create New Sequence</h2>
        <form action="{{ route('automation.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-bold mb-1">Sequence Name</label>
                <input type="text" name="name" class="w-full border rounded-lg p-2" placeholder="e.g., Welcome Series" required>
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Triggering Group</label>
                <select name="group_id" class="w-full border rounded-lg p-2">
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 pt-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg font-bold">Create</button>
                <button type="button" onclick="this.closest('#new-sequence-modal').classList.add('hidden')" class="flex-1 bg-gray-100 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection
