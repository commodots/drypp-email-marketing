@extends('layouts.app')
@section('title', 'Create Campaign')
@section('content')

@if ($errors->any())
    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
        {{ $errors->first('msg') }}
    </div>
@endif

<div class="max-w-2xl p-6 mx-auto bg-white rounded shadow">
    {{-- Header Section --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Create New Campaign</h1>
        <a href="{{ route('campaigns.index') }}" class="text-gray-600 hover:underline text-sm">Cancel</a>
    </div>

    <form action="{{ route('campaigns.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-medium mb-1">Campaign Name</label>
            <input type="text" name="name" id="name" class="w-full p-2 border rounded focus:ring focus:ring-blue-200 outline-none" placeholder="e.g. Q1 Newsletter" required>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-1">Campaign Type</label>
            <div class="flex space-x-6">
                <label class="flex items-center cursor-pointer">
                    <input type="radio" name="type" value="cold_call" class="mr-2" required> Cold Call
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="radio" name="type" value="email_marketing" class="mr-2" required> Email Marketing
                </label>
            </div>
        </div>

        <div class="mb-4">
            <label for="email_subject" class="block text-gray-700 font-medium mb-1">Email Subject</label>
            <input type="text" name="email_subject" id="email_subject" class="w-full p-2 border rounded focus:ring focus:ring-blue-200 outline-none" placeholder="Enter subject line">
        </div>

        <div class="mb-6">
            <label for="email_body" class="block text-gray-700 font-medium mb-1">Email Body</label>
            <textarea name="email_body" id="email_body" rows="6" class="w-full p-2 border rounded focus:ring focus:ring-blue-200 outline-none" placeholder="Write your content here..."></textarea>
        </div>

        <button type="submit" class="w-full md:w-auto px-6 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 transition font-semibold">
            Save as Draft
        </button>
    </form>
</div>
@endsection