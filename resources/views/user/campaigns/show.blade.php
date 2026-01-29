@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <a href="{{ route('campaigns.index') }}" class="text-blue-600 font-bold">&larr; Back to Campaigns</a>
        <span class="bg-gray-100 px-3 py-1 rounded text-xs font-bold uppercase">{{ $campaign->status }}</span>
    </div>

    
    @if(session('campaign_body') && $campaign->format === 'html')
        <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500 shadow-sm animate-pulse">
            <h3 class="font-bold text-blue-800 text-sm mb-2"> Newest Edit (Session Preview)</h3>
            <div class="bg-white p-4 border rounded shadow-inner">
                {!! session('campaign_body') !!}
            </div>
            <p class="text-[10px] text-blue-600 mt-2 font-mono">This preview will disappear when you refresh the page.</p>
        </div>
    @endif

    <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
        <div class="p-6 border-b bg-gray-50">
            <h1 class="text-xl font-bold">{{ $campaign->name }}</h1>
            <p class="text-sm text-gray-500">Subject: {{ $campaign->emailContent->subject ?? 'N/A' }}</p>
        </div>
        
        <div class="p-6">
            <label class="text-xs font-bold text-gray-400 uppercase block mb-2">Stored Content (Database)</label>
            <div class="border rounded bg-white overflow-hidden min-h-[300px]">
                @if($campaign->format === 'html')
                    <iframe class="w-full h-96 border-0" srcdoc="{{ $campaign->emailContent->body ?? '' }}"></iframe>
                @else
                    <div class="p-4 font-mono text-sm whitespace-pre-wrap text-gray-800">{{ $campaign->emailContent->body ?? '' }}</div>
                @endif
            </div>
        </div>

        @if($campaign->status === 'draft')
        <div class="p-4 bg-gray-50 border-t flex justify-between">
            <form action="{{ route('campaigns.destroy', $campaign) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this draft?');">
                @csrf @method('DELETE')
                <button class="text-red-600 font-bold text-sm hover:underline">Delete Draft</button>
            </form>
            <a href="{{ route('campaigns.edit', $campaign) }}" class="bg-blue-100 text-blue-700 px-4 py-2 rounded font-bold text-sm hover:bg-blue-200 transition">
                Edit Content
            </a>
        </div>
        @endif
    </div>
</div>
@endsection