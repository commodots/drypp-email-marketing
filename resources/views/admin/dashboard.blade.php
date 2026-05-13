@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<div class="space-y-6">
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-6 bg-white rounded shadow-sm border-t-4 border-red-500">
            <h3 class="text-gray-500 font-medium">Total Users</h3>
            <p class="text-3xl font-bold">{{ $totalUsers }}</p>
        </div>
        <div class="p-6 bg-white rounded shadow-sm border-t-4 border-blue-500">
            <h3 class="text-gray-500 font-medium">Active Campaigns</h3>
            <p class="text-3xl font-bold">{{ $activeCampaigns }}</p>
        </div>
        <div class="p-6 bg-white rounded shadow-sm border-t-4 border-yellow-500">
            <h3 class="text-gray-500 font-medium">Emails Sent Today</h3>
            <p class="text-3xl font-bold">{{ number_format($emailsToday) }}</p>
        </div>
        <div class="p-6 bg-white rounded shadow-sm border-t-4 border-green-500">
            <h3 class="text-gray-500 font-medium">SMTP Health</h3>
            <p class="text-3xl font-bold">{{ $smtpHealth }} Online</p>
        </div>
    </div>

    <!-- Phase 9: SMTP Health & Placement Table -->
    <div class="p-6 bg-white rounded shadow-sm border border-blue-600">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Inbox Health & Deliverability</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b text-gray-400 uppercase text-[10px] tracking-widest">
                        <th class="pb-3 px-2">Inbox</th>
                        <th class="pb-3 px-2">Health Score</th>
                        <th class="pb-3 px-2">Stats (24h)</th>
                        <th class="pb-3 px-2">Placement</th>
                        <th class="pb-3 px-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($smtps as $smtp)
                    <tr class="hover:bg-gray-50">
                        <td class="py-4 px-2 font-medium">{{ $smtp->name }}</td>
                        <td class="py-4 px-2">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $smtp->health_score < 40 ? 'bg-red-500' : ($smtp->health_score < 75 ? 'bg-yellow-500' : 'bg-green-500') }}" 
                                     style="width: {{ $smtp->health_score }}%"></div>
                            </div>
                            <span class="text-[10px] font-bold">{{ $smtp->health_score }}%</span>
                        </td>
                        <td class="py-4 px-2 text-xs text-gray-600">
                            Sent: {{ $smtp->sent_last_24h }} | Opens: {{ $smtp->opens_last_24h }} | Bounces: {{ $smtp->bounces_last_24h }}
                        </td>
                        <td class="py-4 px-2">
                            <span class="text-green-600 font-bold">Inbox: {{ $smtp->inbox_hits ?? 0 }}</span> / 
                            <span class="text-red-500 font-bold">Spam: {{ $smtp->spam_hits ?? 0 }}</span>
                        </td>
                        <td class="py-4 px-2">
                            @if($smtp->is_blocked)
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-[10px] font-bold uppercase">Blocked</span>
                            @elseif($smtp->health_score < 50)
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-[10px] font-bold uppercase">Warning</span>
                            @else
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold uppercase">Healthy</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection