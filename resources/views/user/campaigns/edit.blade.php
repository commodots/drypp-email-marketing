@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold">Edit Draft: {{ $campaign->name }}</h2>
        <a href="{{ route('campaigns.index') }}" class="text-gray-500 hover:underline">Cancel</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <form action="{{ route('campaigns.update', $campaign) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                {{-- Campaign Name --}}
                <div>
                    <label class="block text-sm font-semibold mb-1">Campaign Name</label>
                    <input type="text" name="name" value="{{ $campaign->name }}" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                </div>

                {{-- Subject --}}
                <div>
                    <label class="block text-sm font-semibold mb-1">Subject</label>
                    <input type="text" name="subject" value="{{ $campaign->emailContent->subject ?? '' }}" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                </div>

                {{-- Format Toggle --}}
                <div>
                    <label class="block text-sm font-semibold mb-2">Format</label>
                    <div class="flex gap-4">
                        <label><input type="radio" name="format" value="html" {{ ($campaign->emailContent->format ?? 'html') == 'html' ? 'checked' : '' }}> HTML</label>
                        <label><input type="radio" name="format" value="text" {{ ($campaign->emailContent->format ?? 'html') == 'text' ? 'checked' : '' }}> Text</label>
                    </div>
                </div>

                {{-- Body --}}
                <div>
                    <label class="block text-sm font-semibold mb-1">Email Content</label>
                    <textarea name="body" rows="10" class="w-full border-gray-300 rounded-lg font-mono text-sm" required>{{ $campaign->emailContent->body ?? '' }}</textarea>
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t">
                    <button type="submit" class="bg-blue-600 text-white px-8 py-2 rounded-lg font-bold">Update Draft</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection