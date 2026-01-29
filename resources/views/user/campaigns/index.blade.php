@extends('layouts.app')
@section('title', 'Campaigns')
@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">Campaigns</h2>
        <a href="{{ route('campaigns.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold">+ New Campaign</a>
    </div>

    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs font-bold uppercase text-gray-500 border-b">
                <tr>
                    <th class="p-4">Name</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Progress</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($campaigns as $campaign)
                <tr>
                    <td class="p-4 font-medium">{{ $campaign->name }}</td>
                    <td class="p-4 text-xs uppercase font-bold">{{ $campaign->status }}</td>
                    <td class="p-4">
                        <div class="w-full bg-gray-200 rounded-full h-2 flex overflow-hidden">
                            <div class="bg-blue-600 h-full" style="width: {{ $campaign->progress }}%"></div>
                        </div>
                        <span class="text-[10px] text-gray-500">{{ $campaign->sent }} / {{ $campaign->total_emails }} sent</span>
                    </td>
                    <td class="p-4 text-right flex justify-end gap-4 items-center">
                        <a href="{{ route('campaigns.show', $campaign) }}" class="text-blue-600 text-xs font-bold">View</a>
                        
                        @if($campaign->status == 'draft')
                        <form action="{{ route('campaigns.send', $campaign) }}" method="POST" class="flex gap-2">
                            @csrf
                            <select name="group_id" class="text-[10px] border-gray-300 rounded p-1" required>
                                <option value="">Select Group</option>
                                @foreach($groups as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                            <button class="bg-green-600 text-white px-3 py-1 rounded text-xs font-bold">Send</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection