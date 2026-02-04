@extends('layouts.app')
@section('title', 'Contacts Management')
@section('content')

<div class="max-w-6xl mx-auto space-y-6">
    
    {{-- Group Filter Bar --}}
    <div class="flex gap-2 overflow-x-auto pb-2">
        <a href="{{ route('contacts.index') }}" class="px-4 py-2 rounded-full text-xs font-bold border {{ !request('group') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 hover:border-blue-400' }}">
            All Contacts
        </a>
        @foreach($groups as $group)
            <a href="{{ route('contacts.index', ['group' => $group->id]) }}" 
               class="px-4 py-2 rounded-full text-xs font-bold border transition {{ request('group') == $group->id ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 hover:border-blue-400' }}">
                {{ $group->name }}
            </a>
        @endforeach
    </div>

    <div class="flex flex-col gap-6">
        <div class="flex flex-col lg:flex-row gap-6">
            
            <div class="bg-white p-6 rounded-xl shadow-sm border">
                <h3 class="font-bold text-gray-800 mb-4">
                    Add New Contact
                </h3>
                <form action="{{ route('contacts.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="email" name="email" placeholder="customer@example.com" class="w-full border-gray-300 rounded-lg focus:ring-blue-500" required>
                    
                    <select name="group_id" class="w-full border-gray-300 rounded-lg text-sm text-gray-600">
                        <option value="">(Optional) Add to Group</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                    
                    <button class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition">Add Contact</button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border">
                <h3 class="font-bold text-gray-800 mb-4">
                    Create New Group
                </h3>
                
                <form action="{{ route('groups.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="text" name="name" placeholder="e.g. Q1 VIPs" class="w-full border-gray-300 rounded-lg focus:ring-blue-500" required>
                    <button class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition">Create Group</button>
                </form>
            </div>

        </div>

        {{--  Contacts List --}}
        <div class="lg:col-span-2">
            <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b text-xs uppercase text-gray-500 font-bold">
                        <tr>
                            <th class="p-4">Recipient</th>
                            <th class="p-4 text-center">Groups</th>
                            <th class="p-4 text-right">Date Added</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($contacts as $contact)
                        <tr class="hover:bg-gray-50">
                            <td class="p-4">
                                <span class="font-bold text-gray-900 block">{{ $contact->email }}</span>
                                <span class="text-[10px] text-gray-400 font-mono uppercase">ID: {{ $contact->id }}</span>
                            </td>
                            <td class="p-4 text-center">
                                @forelse($contact->groups as $g)
                                    <span class="inline-block bg-blue-50 text-blue-700 text-[13px] px-2 py-0.5 rounded font-bold border border-blue-100 mb-1">
                                        {{ $g->name }}
                                    </span>
                                @empty
                                    <span class="inline-block bg-blue-50 text-blue-700 text-[13px] px-2 py-0.5 rounded font-bold border border-blue-100 mb-1">No Groups</span>
                                @endforelse
                            </td>
                            <td class="p-4 text-right">
                                <span class="text-[11px] text-gray-500 font-medium">
                                    {{ $contact->created_at ? $contact->created_at->format('M d, Y') : '---' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="p-12 text-center text-gray-400">
                                <p class="italic">No contacts found.</p>
                                <p class="text-xs">Try clearing your filters or adding a new contact.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection