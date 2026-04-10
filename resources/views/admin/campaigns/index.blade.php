@extends('layouts.app')
@section('title', 'Campaign Control Room')
@section('content')


    <div class="max-w-7xl mx-auto space-y-6">

        {{-- 1. FILTER TABS --}}
        <div>
            <nav class="-mb-px flex space-x-8">
                @foreach (['queued' => 'Queued', 'sending' => 'Sending', 'paused' => 'Paused', 'completed' => 'Completed', 'failed' => 'Failed'] as $key => $label)
                    <a href="{{ route('admin.campaigns.index', ['status' => $key]) }}"
                        class="px-4 py-2 rounded-full text-xs font-bold border transition flex
                   {{ $status === $key ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 hover:border-blue-400' }}">
                        {{ $label }}

                        {{-- Optional: Counter badge --}}
                        <span
                            class="ml-2 py-0.5 px-2 rounded-full text-xs 
                        {{ $status === $key ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ \App\Models\Campaign::where('status', $key)->count() }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- 2. MAIN TABLE --}}
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs font-bold uppercase text-gray-500 border-b">
                    <tr>
                        <th class="p-4 w-1/4">Campaign / Owner</th>
                        <th class="p-4 w-1/4">Progress</th>
                        <th class="p-4 w-1/3">Server Route</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($campaigns as $campaign)
                        <tr class="hover:bg-gray-50 transition group">

                            {{-- COL 1: INFO --}}
                            <td class="p-4">
                                <div class="font-bold text-gray-900">{{ $campaign->name }}</div>
                                <div class="text-xs text-gray-500">{{ $campaign->user->name }}</div>
                                <span
                                    class="mt-1 inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase border
                            {{ $campaign->status == 'sending' ? 'bg-blue-100 text-blue-700 border-blue-200' : '' }}
                            {{ $campaign->status == 'completed' ? 'bg-green-100 text-green-700 border-green-200' : '' }}
                            {{ $campaign->status == 'failed' ? 'bg-red-100 text-red-700 border-red-200' : '' }}
                            {{ $campaign->status == 'queued' ? 'bg-yellow-100 text-yellow-700 border-yellow-200' : '' }}"
                             {{ $campaign->status == 'paused' ? 'bg-gray-100 text-gray-700 border-gray-200' : '' }}">
                                    {{ $campaign->status }}
                                </span>
                            </td>

                            {{-- COL 2: PROGRESS --}}
                            <td class="p-4">
                                @php
                                    $percent =
                                        $campaign->total_emails > 0
                                            ? ($campaign->sent / $campaign->total_emails) * 100
                                            : 0;
                                    $isFinished =
                                        $campaign->sent >= $campaign->total_emails && $campaign->total_emails > 0;
                                @endphp

                                <div class="flex justify-between text-xs font-bold text-gray-600 mb-1">
                                    <span>{{ number_format($campaign->sent) }} /
                                        {{ number_format($campaign->total_emails) }}</span>
                                    <span>{{ round($percent) }}%</span>
                                </div>

                                <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                    <div class="h-full transition-all duration-700 
                                {{ $isFinished ? 'bg-green-500' : 'bg-blue-600' }}"
                                        style="width: {{ $percent }}%">
                                    </div>
                                </div>

                                @if ($isFinished)
                                    <div class="text-[10px] text-green-600 font-bold mt-1 text-right">Completed</div>
                                @endif
                            </td>

                            {{-- COL 3: SERVER CONTROL --}}
                            <td class="p-4">
                                {{-- Logic: Only show controls if Queued or Sending --}}
                                @if (in_array($campaign->status, ['queued', 'sending', 'paused']))
                                    <form action="{{ route('admin.campaigns.assign', $campaign) }}" method="POST"
                                        class="flex flex-col gap-1">
                                        @csrf
                                        <div class="flex gap-2">
                                            <select name="smtp_id"
                                                class="text-xs border-gray-300 rounded bg-gray-50 w-full focus:ring-blue-500">
                                                <option value="">-- Assign Server --</option>
                                                @foreach ($smtps as $smtp)
                                                    <option value="{{ $smtp->id }}"
                                                        {{ $campaign->smtp_id == $smtp->id ? 'selected' : '' }}>
                                                        {{ $smtp->name }} (Lim: {{ $smtp->daily_limit }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button
                                                class="bg-blue-600 text-white px-3 py-1 rounded text-xs font-bold hover:bg-blue-700 whitespace-nowrap shadow-sm">
                                                {{ $campaign->status == 'queued' ? 'Launch' : 'Switch' }}
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    {{-- If Sent/Failed, just show the server name static --}}
                                    <div class="text-sm text-gray-600">
                                        <span class="font-bold text-gray-400 text-xs uppercase">Routed via:</span><br>
                                        {{ $campaign->smtp->name ?? 'N/A' }}
                                    </div>
                                @endif
                            </td>

                            {{-- COL 4: EMERGENCY ACTIONS --}}
                            <td class="p-4 text-right">
                                @if ($campaign->status == 'sending' || $campaign->status == 'paused')
                                    <a href="{{ route('admin.campaigns.toggle', $campaign) }}"
                                        class="inline-block px-3 py-1.5 rounded border font-bold text-xs transition
                               {{ $campaign->status === 'paused'
                                   ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100'
                                   : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100' }}">
                                        {{ $campaign->status === 'paused' ? 'Resume' : 'Pause' }}
                                    </a>
                                @else
                                    <span class="text-gray-300 text-xs italic">No actions</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-12 text-center text-gray-400">
                                <div>
                                    <span>No campaigns found in '{{ ucfirst($status) }}'.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
