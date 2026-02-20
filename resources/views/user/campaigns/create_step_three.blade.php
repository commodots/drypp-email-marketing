@extends('layouts.app')
@section('title', 'Step 3: Review & Launch')

@section('content')
<div class="max-w-5xl mx-auto py-8">
    
    {{-- Steps Header --}}
    <div class="flex items-center gap-4 mb-8 text-sm font-bold text-gray-400">
        <span class="text-gray-500">1. Setup</span>
        <span>→</span>
        <span class="text-gray-500">2. Content</span>
        <span>→</span>
        <span class="text-blue-600">3. Review & Launch</span>
    </div>

    <div class="bg-white rounded-xl shadow-lg border overflow-hidden">
        
        {{-- Summary Header --}}
        <div class="p-6 border-b bg-gray-50 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Final Review</h2>
                <p class="text-sm text-gray-500">
                    You are about to send <strong>"{{ $data['subject'] }}"</strong> to:
                    <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide">
                        {{ $groupName }}
                    </span>
                </p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-black text-gray-900">{{ number_format($totalCount) }}</div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wide">Recipients Found</div>
            </div>
        </div>

        {{-- Recipient Table --}}
        <div class="p-0">
            @if($recipients->isEmpty())
                <div class="p-12 text-center text-red-500 font-bold">
                     No contacts found matching your criteria. Go back and check your filters.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-100 text-xs uppercase font-bold text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Email Address</th>
                                <th class="px-6 py-3">Added On</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recipients as $contact)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-medium text-gray-900">{{ $contact->email }}</td>
                                    <td class="px-6 py-3">{{ $contact->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Links (if > 50) --}}
                @if($recipients->hasPages())
                    <div class="p-4 border-t bg-gray-50">
                        {{ $recipients->links() }} 
                        <p class="text-xs text-center text-gray-400 mt-2">
                            (Showing first 50. All {{ number_format($totalCount) }} will be sent.)
                        </p>
                    </div>
                @endif
            @endif
        </div>

        {{-- FINAL ACTIONS --}}
        <div class="p-6 bg-gray-50 border-t flex flex-col sm:flex-row gap-4 justify-between items-center">
            
            {{-- Back to Step 2 (Form Submission to preserve data) --}}
            <form action="{{ route('campaigns.stepTwo') }}" method="POST">
                @csrf
                {{-- Carry ALL Data Back --}}
                @foreach($data as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $v) <input type="hidden" name="{{ $key }}[]" value="{{ $v }}"> @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                
                {{-- We use 'preview' action to trigger the stepTwo view renderer --}}
                <button type="submit" name="action" value="preview" class="text-gray-500 hover:text-gray-800 font-bold text-sm px-4">
                    ← Edit Content
                </button>
            </form>

            {{-- DRAFT & LAUNCH BUTTONS --}}
            <form action="{{ route('campaigns.store') }}" method="POST" class="flex flex-wrap gap-3">
                @csrf
                {{-- Carry ALL Data Forward to Store --}}
                @foreach($data as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $v) <input type="hidden" name="{{ $key }}[]" value="{{ $v }}"> @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                {{-- Save Draft Button --}}
                <button type="submit" name="action" value="draft" class="bg-white text-blue-600 border border-blue-600 px-6 py-3 rounded-lg font-bold hover:bg-blue-50 transition flex items-center justify-center">
                    Save as Draft
                </button>

                {{-- Launch Button --}}
                <button type="submit" name="action" value="send" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-bold shadow-lg hover:bg-blue-700 hover:shadow-xl transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2" {{ $recipients->isEmpty() ? 'disabled' : '' }}>
                    <span>Send Campaign</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection