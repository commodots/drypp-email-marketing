@extends('layouts.app')
@section('title', 'Campaigns')
@section('content')

<div class="max-w-6xl mx-auto space-y-6">
    <div>
        <a href="{{ route('campaigns.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold shadow-sm hover:bg-blue-700 transition">+ New Campaign</a>
    </div>

    {{--  Added an ID and a quick fade-out script --}}
    @if(session('success'))
        <div id="flash-message" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 font-bold border border-green-200 shadow-sm transition-opacity duration-500 ease-in-out">
            {{ session('success') }}
        </div>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    let flash = document.getElementById('flash-message');
                    if(flash) {
                        flash.style.opacity = '0'; // Trigger the CSS fade
                        setTimeout(() => flash.remove(), 500); // Remove from DOM after fade finishes
                    }
                }, 4000); // 4 seconds before it starts fading
            });
        </script>
    @endif

    <div>
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            @foreach(['all' => 'All Campaigns', 'draft' => 'Drafts', 'queued' => 'Queued', 'sending' => 'Sending', 'completed' => 'Completed'] as $key => $label)
                <a href="{{ route('campaigns.index', ['status' => $key === 'all' ? null : $key]) }}" 
                   class="px-4 py-2 rounded-full text-xs font-bold border transition
                   {{ (request('status') == $key) || (request('status') == null && $key == 'all') 
                      ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-gray-600 hover:border-blue-400' }}">
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
            <tbody class="divide-y divide-gray-100">
                @foreach($campaigns as $campaign)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-4 font-medium text-gray-800">{{ $campaign->name }}</td>
                    <td class="p-4 text-xs uppercase font-bold text-gray-600">{{ $campaign->status }}</td>
                    <td class="p-4">
                        <div class="w-full bg-gray-200 rounded-full h-2 flex overflow-hidden mb-1">
                            <div class="bg-blue-600 h-full transition-all duration-500" style="width: {{ $campaign->progress }}%"></div>
                        </div>
                        <span class="text-[10px] text-gray-500 font-medium">{{ $campaign->sent }} / {{ $campaign->total_emails }} sent</span>
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex justify-end gap-4 items-center">
                            {{-- View Link --}}
                            <a href="{{ route('campaigns.show', $campaign) }}" class="text-blue-600 hover:text-blue-800 hover:underline text-xs font-bold transition">View</a>
                            
                            {{-- Send Campaign Link (Only for Drafts) --}}
                            @if($campaign->status == 'draft')
                                <form action="{{ route('campaigns.send', $campaign) }}" method="POST" class="m-0 p-0 inline-block" onsubmit="return confirm('Are you sure you want to send this drafted campaign?');">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 hover:underline text-xs font-bold bg-transparent border-none cursor-pointer p-0 transition">
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