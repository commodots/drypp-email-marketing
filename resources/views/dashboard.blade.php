@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@if ($errors->any())
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
        {{ $errors->first() }}
    </div>
@endif

<div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-3">
    <div class="p-4 bg-white rounded shadow">
        <h3 class="text-gray-500">Active Package</h3>
        <p class="text-xl font-bold">{{ $user->package ? $user->package->name : 'No active package' }}</p>
    </div>

    <div class="p-4 bg-white rounded shadow">
        <h3 class="text-gray-500">Emails Remaining</h3>
        <p class="text-xl font-bold">{{ $user->emailsRemaining() }}</p>
    </div>

    <div class="p-4 bg-white rounded shadow">
        <h3 class="text-gray-500">Leads Remaining</h3>
       <p class="text-xl font-bold">{{ $user->leadsRemaining() }}</p>
    </div>
</div>

{{-- Quick Actions --}}
<div class="flex gap-4 mb-6">
    @if(!$user->package)
        <a href="{{ route('billing.index') }}" class="px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700">
            Buy Package
        </a>
    @else
        <a href="{{ route('campaigns.create') }}" class="px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700">
            + Create Campaign
        </a>
        <a href="{{ route('leads.index') }}" class="px-4 py-2 text-white transition bg-gray-700 rounded hover:bg-gray-800">
            Buy Leads
        </a>
    @endif
</div>

<div class="p-4 bg-white rounded shadow">
    <h3 class="mb-2 font-semibold">Recent Campaigns</h3>
    @if($recentCampaigns->isEmpty())
        <p class="text-gray-500">No campaigns yet. <a href="{{ route('campaigns.create') }}" class="text-blue-600">Create your first campaign</a></p>
    @else
        <ul class="space-y-2">
            @foreach($recentCampaigns as $campaign)
                <li class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">{{ $campaign->name }}</p>
                        <p class="text-sm text-gray-500">{{ ucfirst($campaign->type) }} • {{ ucfirst($campaign->status) }}</p>
                    </div>
                    <span class="text-sm">{{ $campaign->emails_count }} emails</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection