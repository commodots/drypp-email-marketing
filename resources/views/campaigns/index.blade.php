@extends('layouts.app')
@section('title', 'Campaigns')
@section('content')

@if ($errors->any())
    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
        {{ $errors->first('msg') }}
    </div>
@endif

<div class="max-w-4xl p-6 mx-auto bg-white rounded shadow">
    <h1 class="mb-4 text-2xl font-bold">Your Campaigns</h1>
    <a href="{{ route('campaigns.create') }}" class="inline-block px-4 py-2 mb-4 text-white bg-blue-600 rounded hover:bg-blue-700">+ Create Campaign</a>
    @if($campaigns->isEmpty())
        <p class="text-gray-500">No campaigns yet. <a href="{{ route('campaigns.create') }}" class="text-blue-600">Create your first campaign</a></p>
    @else
        <table class="w-full table-auto">
            <thead>
                <tr class="border-b">
                    <th class="p-2 text-left">Name</th>
                    <th class="p-2 text-left">Type</th>
                    <th class="p-2 text-left">Status</th>
                    <th class="p-2 text-left">Emails</th>
                    <th class="p-2 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($campaigns as $campaign)
                    <tr class="border-b">
                        <td class="p-2">{{ $campaign->name }}</td>
                        <td class="p-2">{{ ucfirst(str_replace('_', ' ', $campaign->type)) }}</td>
                        <td class="p-2">
                            <span class="px-2 py-1 rounded text-sm {{ $campaign->status == 'draft' ? 'bg-yellow-200 text-yellow-800' : 'bg-green-200 text-green-800' }}">
                                {{ ucfirst($campaign->status) }}
                            </span>
                        </td>
                        <td class="p-2">{{ $campaign->emails_count }}</td>
                        <td class="p-2">
                            @if($campaign->status == 'draft')
                                <form action="{{ route('campaigns.send', $campaign) }}" method="POST" class="inline">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="text-blue-600 hover:underline">Mark as Sent</button>
                                </form>
                            @else
                                <a href="#" class="text-blue-600">View</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection