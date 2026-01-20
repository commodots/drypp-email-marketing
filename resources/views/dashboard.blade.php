@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-3">
    <div class="p-4 bg-white rounded shadow">
        <h3 class="text-gray-500">Active Package</h3>
        <p class="text-xl font-bold">None</p>
    </div>

    <div class="p-4 bg-white rounded shadow">
        <h3 class="text-gray-500">Emails Remaining</h3>
        <p class="text-xl font-bold">0</p>
    </div>

    <div class="p-4 bg-white rounded shadow">
        <h3 class="text-gray-500">Leads Available</h3>
        <p class="text-xl font-bold">0</p>
    </div>
</div>

{{-- Quick Actions --}}
<div class="flex gap-4 mb-6">
    <a href="{{ route('campaigns.create') }}" class="px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700">
        + Create Campaign
    </a>
    <a href="{{ route('leads.index') }}" class="px-4 py-2 text-white transition bg-gray-700 rounded hover:bg-gray-800">
        Buy Leads
    </a>
</div>

<div class="p-4 bg-white rounded shadow">
    <h3 class="mb-2 font-semibold">Recent Campaigns</h3>
    <p class="text-gray-500">No campaigns yet.</p>
</div>
@endsection