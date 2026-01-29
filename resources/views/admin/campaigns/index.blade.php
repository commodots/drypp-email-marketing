@extends('layouts.app')
@section('title', 'Campaign Control Room')
@section('content')

<div class="max-w-6xl mx-auto space-y-8">
    
    <div>
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
            <span class="w-3 h-3 bg-yellow-400 rounded-full animate-pulse"></span>
            Waiting for Assignment ({{ $queuedCampaigns->count() }})
        </h2>

        <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-left">
                <thead class="bg-yellow-50 border-b text-xs uppercase text-yellow-800 font-bold">
                    <tr>
                        <th class="p-4">User</th>
                        <th class="p-4">Campaign</th>
                        <th class="p-4">Queue Size</th>
                        <th class="p-4">Assign SMTP & Start</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($queuedCampaigns as $campaign)
                    <tr class="bg-yellow-50/50">
                        <td class="p-4">{{ $campaign->user->name }}</td>
                        <td class="p-4 font-medium">{{ $campaign->name }}</td>
                        <td class="p-4 font-mono">{{ number_format($campaign->total_emails) }} emails</td>
                        <td class="p-4">
                            <form action="{{ route('admin.campaigns.assign', $campaign) }}" method="POST" class="flex gap-2">
                                @csrf
                                <select name="smtp_id" class="text-sm border-gray-300 rounded-lg py-1 px-2" required>
                                    <option value="">Select Server...</option>
                                    @foreach($smtps as $smtp)
                                        <option value="{{ $smtp->id }}">{{ $smtp->name }} ({{ $smtp->daily_limit }})</option>
                                    @endforeach
                                </select>
                                <button class="bg-green-600 text-white px-4 py-1 rounded-lg font-bold text-sm hover:bg-green-700 shadow-sm">
                                    Start Sending
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-6 text-center text-gray-500">No campaigns waiting in queue.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ACTIVE MONITORING --}}
    <div>
        <h2 class="text-xl font-bold mb-4 text-gray-700">Live Monitor</h2>
        <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b text-xs uppercase text-gray-500 font-bold">
                    <tr>
                        <th class="p-4">Campaign</th>
                        <th class="p-4">SMTP Used</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Progress</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($activeCampaigns as $campaign)
                    <tr>
                        <td class="p-4">
                            <div class="font-bold">{{ $campaign->name }}</div>
                            <div class="text-xs text-gray-500">{{ $campaign->user->name }}</div>
                        </td>
                        <td class="p-4 text-sm">{{ $campaign->smtp->name ?? 'N/A' }}</td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded text-xs font-bold uppercase 
                                {{ $campaign->status == 'sending' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100' }}">
                                {{ $campaign->status }}
                            </span>
                        </td>
                        <td class="p-4 font-mono text-sm">
                            {{ $campaign->sent }} / {{ $campaign->total_emails }}
                        </td>
                        <td class="p-4">
                            <a href="{{ route('admin.campaigns.toggle', $campaign) }}" class="text-blue-600 text-sm hover:underline">
                                {{ $campaign->status === 'paused' ? 'Resume' : 'Pause' }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection