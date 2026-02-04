@extends('layouts.app')
@section('title', 'Campaigns')
@section('content')


<div class="max-w-6xl mx-auto space-y-6">
    <div>
        <a href="{{ route('campaigns.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold">+ New Campaign</a>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 font-bold border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div>
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            @foreach(['all' => 'All Campaigns', 'draft' => 'Drafts', 'queued' => 'Queued', 'sending' => 'Sending', 'completed' => 'Completed'] as $key => $label)
                <a href="{{ route('campaigns.index', ['status' => $key === 'all' ? null : $key]) }}" 
                   class="px-4 py-2 rounded-full text-xs font-bold border transition
                   {{ (request('status') == $key) || (request('status') == null && $key == 'all') 
                      ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 hover:border-blue-400' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
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
                    <td class="p-4 text-right">
                        <div class="flex justify-end gap-4 items-start">
                            <a href="{{ route('campaigns.show', $campaign) }}" class="text-blue-600 text-xs font-bold py-1">View</a>
                            
                            @if($campaign->status == 'draft')
                            <form action="{{ route('campaigns.send', $campaign) }}" method="POST" class="bg-gray-50 p-3 rounded-lg border border-gray-200 w-64 text-left">
                                @csrf
                                
                                <input type="radio" id="type-group-{{ $campaign->id }}" name="recipient_type" value="group" checked class="radio-group hidden">
                                <input type="radio" id="type-indiv-{{ $campaign->id }}" name="recipient_type" value="individual" class="radio-individual hidden">

                                
                                <div class="tab-labels flex gap-1 bg-gray-200 p-1 rounded-md mb-2">
                                    <label for="type-group-{{ $campaign->id }}" class="label-group flex-1 text-center cursor-pointer text-[10px] font-bold py-1 rounded transition">
                                        Group
                                    </label>
                                    <label for="type-indiv-{{ $campaign->id }}" class="label-individual flex-1 text-center cursor-pointer text-[10px] font-bold py-1 rounded transition">
                                        Individuals
                                    </label>
                                </div>
                                <button class="w-full mt-3 bg-blue-600 text-white px-4 py-1.5 rounded text-xs font-bold hover:bg-blue-700 transition">
                                    Send Campaign
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection