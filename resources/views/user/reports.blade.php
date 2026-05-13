@extends('layouts.app')
@section('title', 'Reports')
@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">

            <div class="flex items-center space-x-4">
                <form action="{{ route('reports.toggle') }}" method="POST" class="flex items-center space-x-2">
                    @csrf
                    <span class="text-sm text-gray-600">Auto email report</span>
                    <input type="checkbox" name="enabled" onchange="this.form.submit()" class="rounded text-blue-600"
                        {{ auth()->user()->auto_reports ? 'checked' : '' }}>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($campaigns as $campaign)
                <div class="p-6 bg-white border shadow-sm rounded-xl">
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">{{ $campaign->name }}</h3>
                            <p class="text-xs text-gray-500">Sent to {{ number_format($campaign->sent) }} recipients</p>
                        </div>
                        <a href="{{ route('reports.export', $campaign) }}"
                            class="text-xs text-blue-600 hover:underline">Export CSV</a>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Open Rate</p>
                            <p class="text-2xl font-black text-blue-600">
                                {{ $campaign->open_rate }}%
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Click Rate</p>
                            <p class="text-2xl font-black text-green-600">
                                {{ $campaign->click_rate }}%
                            </p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-50">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Estimated Bounces</span>
                            <span class="font-semibold text-red-500">{{ ceil($campaign->sent * 0.01) }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-700 col-span-3">No sending activity to report yet.</p>
            @endforelse
        </div>
    </div>
@endsection
