@extends('layouts.app')
@section('title', 'Manage Sequence: ' . $automation->name)
@section('content')

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex justify-between items-center bg-white p-6 rounded-xl border shadow-sm">
        <div>
            <h2 class="text-xl font-bold">Sequence: {{ $automation->name }}</h2>
            <p class="text-sm text-gray-500">Triggered by: {{ $automation->group->name ?? 'No Group' }}</p>
        </div>
        <a href="{{ route('automation.index') }}" class="text-blue-600 hover:underline text-sm">← Back to Sequences</a>
    </div>

    {{-- Add New Step Form --}}
    <div class="bg-white p-6 rounded-xl border shadow-sm">
        <h3 class="text-lg font-bold mb-4">Add New Step</h3>
        <form action="{{ route('automation.addStep', $automation) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-bold mb-1">Delay (Days after previous step)</label>
                <input type="number" name="delay_days" value="0" min="0" class="w-full border rounded-lg p-2" required>
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Subject</label>
                <input type="text" name="subject" class="w-full border rounded-lg p-2" required>
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Body (HTML)</label>
                <textarea name="body" rows="8" class="w-full border rounded-lg p-2 font-mono" required></textarea>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold">Add Step</button>
        </form>
    </div>

    {{-- Existing Steps --}}
    <div class="space-y-4">
        @forelse($automation->steps->sortBy('delay_days') as $step)
        <div class="bg-white p-6 rounded-xl border shadow-sm" id="step-{{ $step->id }}">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg">Step {{ $loop->iteration }}: {{ $step->subject }}</h3>
                <div class="flex gap-2">
                    <button onclick="toggleEdit('{{ $step->id }}')" class="text-sm text-blue-600 hover:underline">Edit</button>
                    <form action="{{ route('automation.destroyStep', $step) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this step?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                    </form>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">Delay: {{ $step->delay_days }} day(s)</p>
            <div class="prose max-w-none text-sm text-gray-800 border-t pt-4">
                {!! $step->body !!}
            </div>

            {{-- Edit Form (Hidden by default) --}}
            <div id="edit-form-{{ $step->id }}" class="hidden mt-6 pt-6 border-t border-gray-200">
                <h4 class="font-bold mb-4">Edit Step</h4>
                <form action="{{ route('automation.updateStep', $step) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-bold mb-1">Delay (Days after previous step)</label>
                        <input type="number" name="delay_days" value="{{ $step->delay_days }}" min="0" class="w-full border rounded-lg p-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Subject</label>
                        <input type="text" name="subject" value="{{ $step->subject }}" class="w-full border rounded-lg p-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Body (HTML)</label>
                        <textarea name="body" rows="8" class="w-full border rounded-lg p-2 font-mono" required>{{ $step->body }}</textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg font-bold">Update Step</button>
                        <button type="button" onclick="toggleEdit('{{ $step->id }}')" class="bg-gray-100 py-2 px-4 rounded-lg">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        @empty
        <p class="text-gray-700">No steps added to this sequence yet.</p>
        @endforelse
    </div>
</div>

<script>
    function toggleEdit(stepId) {
        const editForm = document.getElementById(`edit-form-${stepId}`);
        editForm.classList.toggle('hidden');
    }
</script>
@endsection
