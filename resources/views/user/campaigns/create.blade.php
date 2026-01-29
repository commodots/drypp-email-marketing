@extends('layouts.app')
@section('title', 'Create New Campaign')
@section('content')

@if ($errors->any())
    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
        {{ $errors->first() }}
    </div>
@endif

<div class="max-w-4xl mx-auto py-8">
    <div class="mb-6">
        <p class="text-gray-600">Fill in the details below to set up your campaign.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <form action="{{ route('campaigns.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                
                {{-- Campaign Name --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Campaign Name</label>
                    <input type="text" name="name" placeholder="e.g. January Newsletter" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                </div>

                {{-- Campaign Type --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Campaign Type</label>
                    <div class="flex space-x-6">
                        <label class="flex items-center cursor-pointer group">
                            <input type="radio" name="type" value="cold" class="w-4 h-4 text-blue-600 border-gray-300" required>
                            <span class="ml-2 text-gray-700">Cold Email</span>
                        </label>
                        <label class="flex items-center cursor-pointer group">
                            <input type="radio" name="type" value="bulk" class="w-4 h-4 text-blue-600 border-gray-300" required>
                            <span class="ml-2 text-gray-700">Bulk Marketing</span>
                        </label>
                    </div>
                </div>

                {{-- Subject Line --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Subject</label>
                    <input type="text" name="subject" placeholder="Subject" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                </div>

                {{-- Contact Group Selection --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Target Audience</label>
                    <select name="group_id" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                        <option value="">Select a Contact Group...</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>

             
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email Format</label>
                    <select name="format" class="w-full border-gray-300 rounded-lg shadow-sm">
                        <option value="html">HTML (Preview available after save)</option>
                        <option value="text">Plain Text</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Select HTML to enable preview mode.</p>
                </div>

                {{-- Content Body --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Content</label>
                    <textarea name="body" class="w-full h-64 border-gray-300 rounded-lg font-mono text-sm" placeholder="Type your content here..." required></textarea>
                </div>

                {{-- Form Actions --}}
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('campaigns.index') }}" class="px-6 py-2 text-gray-600 font-medium hover:text-gray-800 transition">Cancel</a>
                    <button type="submit" class="bg-blue-600 text-white px-8 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-md">
                        Save as Draft
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection