@extends('layouts.app')
@section('title', 'Campaign Preview')
@section('content')

<div class="max-w-7xl mx-auto space-y-6">

    {{--  HEADER ACTIONS --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        
        <div class="flex items-center gap-3">
            <a href="{{ route('campaigns.index') }}" class="text-blue-600 font-bold hover:underline">&larr; Back to Campaigns</a>
            
            <span class="px-3 py-1 rounded text-xs font-bold uppercase border ml-2
                {{ $campaign->status === 'draft' ? 'bg-gray-100 text-gray-600 border-gray-200' : 'bg-green-50 text-green-700 border-green-200' }}">
                Status: {{ $campaign->status }}
            </span>
        </div>
        
        {{-- Action Buttons (Only show for drafts) --}}
        @if($campaign->status === 'draft')
            <div class="flex items-center gap-4">
                <a href="{{ route('campaigns.edit', $campaign) }}" class="text-gray-500 hover:text-blue-600 text-sm font-bold underline">
                    Edit Content
                </a>
                
                {{--Delete Draft Form --}}
                <form action="{{ route('campaigns.destroy', $campaign) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this draft? This cannot be undone.');" class="m-0 p-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-bold underline bg-transparent border-none cursor-pointer p-0">
                        Delete Draft
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{--  GRID LAYOUT: Content (Left) vs Recipients (Right) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        {{-- LEFT COLUMN: Message Content --}}
        <div class="lg:col-span-2 space-y-4">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Message Preview
            </h2>

            <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
                <div class="bg-gray-50 p-4 border-b space-y-1">
                    <div class="flex items-start gap-2 text-sm">
                        <span class="font-bold text-gray-500 w-16 text-right">Subject:</span>
                        <span class="font-medium text-gray-900">{{ $campaign->emailContent->subject ?? '(No Subject)' }}</span>
                    </div>
                    <div class="flex items-start gap-2 text-sm">
                        <span class="font-bold text-gray-500 w-16 text-right">Format:</span>
                        <span class="uppercase text-xs font-bold bg-blue-100 text-blue-700 px-1.5 rounded">{{ $campaign->format }}</span>
                    </div>
                </div>

                {{-- Content Body --}}
                <div class="bg-white min-h-[400px]">
                    @if(session('campaign_body') && $campaign->format === 'html')
                        {{-- Fresh Session Preview (Raw HTML) --}}
                        <div class="p-6">
                            {!! session('campaign_body') !!}
                        </div>
                    @elseif($campaign->format === 'html')
                        {{-- Database HTML (Iframe for safety) --}}
                        <iframe class="w-full h-[600px] border-0" srcdoc="{{ $campaign->emailContent->body ?? '' }}"></iframe>
                    @else
                        {{-- Plain Text --}}
                        <div class="p-8 font-mono text-sm whitespace-pre-wrap text-gray-800 leading-relaxed bg-white">
                            {{ $campaign->emailContent->body ?? '' }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Recipient List  --}}
        <div class="lg:col-span-1 space-y-4 sticky top-6">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Recipients List
            </h2>

            <div class="bg-white border-2 border-blue-200 rounded-xl shadow-md overflow-hidden">
                {{-- Box Header --}}
                <div class="bg-blue-50 px-4 py-3 border-b border-blue-200 flex justify-between items-center">
                    <span class="font-bold text-blue-900 text-sm">Recipients</span>
                    <span class="bg-blue-600 text-white px-2 py-0.5 rounded-full text-xs font-bold">
                        {{ $totalRecipients }} 
                    </span>
                </div>

                {{-- Scrollable List --}}
                <div class="max-h-[500px] overflow-y-auto custom-scrollbar">
                    <ul class="divide-y divide-blue-50">
                        
                        {{--Now loops through the dynamically generated list --}}
                        @forelse($previewRecipients as $msg)
                            <li class="px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 transition flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 overflow-hidden">
                                    {{-- Status Dot --}}
                                    <span class="w-2 h-2 rounded-full flex-shrink-0 
                                        {{ $msg->status == 'sent' ? 'bg-green-500 shadow-[0_0_5px_rgba(34,197,94,0.5)]' : '' }}
                                        {{ $msg->status == 'pending' ? 'bg-gray-300' : '' }}
                                        {{ $msg->status == 'draft_pending' ? 'bg-gray-200 border border-gray-300' : '' }}
                                        {{ $msg->status == 'failed' ? 'bg-red-500 shadow-[0_0_5px_rgba(239,68,68,0.5)]' : '' }}">
                                    </span>
                                    
                                    {{-- Email Address --}}
                                    <span class="font-mono text-xs truncate text-gray-600">{{ $msg->email }}</span>
                                </div>

                                {{-- Text Status Label --}}
                                <span class="flex-shrink-0 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border
                                    {{ $msg->status == 'sent' ? 'bg-green-50 text-green-700 border-green-200' : '' }}
                                    {{ $msg->status == 'pending' ? 'bg-gray-100 text-gray-500 border-gray-200' : '' }}
                                    {{ $msg->status == 'draft_pending' ? 'bg-gray-50 text-gray-400 border-gray-100' : '' }}
                                    {{ $msg->status == 'failed' ? 'bg-red-50 text-red-700 border-red-200' : '' }}">
                                    
                                    @if($msg->status == 'failed')
                                        Rejected
                                    @elseif($msg->status == 'draft_pending')
                                        Pending
                                    @else
                                        {{ $msg->status }}
                                    @endif
                                </span>
                            </li>
                        @empty
                            <li class="p-6 text-center text-gray-400 italic text-xs">
                                No recipients found in database.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom scrollbar for the preview panel */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>
@endsection