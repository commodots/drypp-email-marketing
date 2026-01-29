@extends('layouts.app')

@section('title', 'User Dashboard')

@section('content')

@if ($errors->any())
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
        {{ $errors->first() }}
    </div>
@endif

<div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-4"> 
    <div class="p-4 bg-white rounded shadow">
        <h3 class="text-gray-500">Active Package</h3>
        <p class="text-xl font-bold">{{ $activePackage }}</p>
    </div>

    <div class="p-4 bg-white rounded shadow text-blue-600">
        <h3 class="text-gray-500">Emails Remaining</h3>
        <p class="text-xl font-bold">{{ number_format($emailsRemaining) }}</p>
    </div>

    <div class="p-4 bg-white rounded shadow">
        <h3 class="text-gray-500">Leads Remaining</h3>
        <p class="text-xl font-bold">{{ number_format($leadsRemaining) }}</p>
    </div>

    <div class="p-4 bg-white rounded shadow">
        <h3 class="text-gray-500 ">Campaigns Running</h3>
        <p class="text-xl font-bold">{{ $runningCampaigns }}</p>
    </div>
</div>

{{-- Quick Actions --}}
<div class="flex gap-4 mb-6">
    @if(!$user->subscription || $user->subscription->status !== 'active')
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
                <li class="flex items-center justify-between border-b pb-2 last:border-0">
                    <div>
                        <p class="font-medium">{{ $campaign->name }}</p>
                        <p class="text-sm text-gray-500">{{ ucfirst($campaign->type) }} • {{ ucfirst($campaign->status) }}</p>
                    </div>
                    
                    <span class="text-sm font-semibold">{{ number_format($campaign->sent) }} sent</span>
                </li>
            @endforeach
        </ul>
    @endif

    {{-- Chart Section --}}
    <div class="p-6 bg-white border rounded shadow-sm mt-6">
        <h3 class="font-bold mb-4">Emails sent last 7 days</h3>
        <div class="flex items-end gap-2 h-48 border-b border-l p-2">
            @forelse($chartData as $data)
                @php 
                    $height = min(($data->total_sent / 1000) * 100, 100); 
                @endphp
                <div class="flex-1 flex flex-col items-center group relative">
                    <span class="absolute -top-8 hidden group-hover:block bg-black text-white text-[10px] px-2 py-1 rounded">
                        {{ number_format($data->total_sent) }}
                    </span>
                    <div class="w-full bg-blue-500 rounded-t transition-all duration-500" style="height: {{ $height }}%"></div>
                    <span class="text-[10px] text-gray-400 mt-1">{{ \Carbon\Carbon::parse($data->date)->format('D') }}</span>
                </div>
            @empty
                <p class="text-gray-400 text-sm w-full text-center">No data for the last 7 days.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection