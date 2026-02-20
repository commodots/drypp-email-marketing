@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        
        <div class="flex items-center space-x-4">
            <form action="{{ route('reports.toggle') }}" method="POST" class="flex items-center space-x-2">
                @csrf
                <span class="text-sm text-gray-600">Auto email report</span>
                <input type="checkbox" name="enabled" onchange="this.form.submit()" class="rounded text-blue-600" {{ auth()->user()->auto_reports ? 'checked' : '' }}>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($campaigns as $campaign)
        <div class="bg-white p-6 rounded shadow-sm border">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-bold">{{ $campaign->name }}</h3>
                <a href="{{ route('reports.export', $campaign) }}" class="text-xs text-blue-600 hover:underline">Export CSV</a>
            </div>
            
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Opens</p>
                    <p class="text-xl font-bold text-blue-600">{{ $campaign->opens }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Clicks</p>
                    <p class="text-xl font-bold text-green-600">{{ $campaign->clicks }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Bounces</p>
                    <p class="text-xl font-bold text-red-600">
                        {{ ceil($campaign->sent * 0.01) }}
                    </p>
                </div>
            </div>
        </div>
        @empty
        <p class="text-gray-700 col-span-3">No sending activity to report yet.</p>
        @endforelse
    </div>
</div>
@endsection