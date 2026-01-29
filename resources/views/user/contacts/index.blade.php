@extends('layouts.app')
@section('title', 'Contacts')
@section('content')

<div class="max-w-4xl mx-auto space-y-6">
    
    {{-- Add Contact Form --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <h3 class="font-bold text-lg mb-4">Add New Contact</h3>
        <form action="{{ route('contacts.store') }}" method="POST" class="flex gap-4">
            @csrf
            <input type="email" name="email" placeholder="Enter email address" class="flex-1 border-gray-300 rounded-lg" required>
            <select name="group_id" class="border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select Group (Optional)</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
            </select>
            <button class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700">Add Contact</button>
        </form>
    </div>

    {{-- Contacts List --}}
    <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b text-xs uppercase text-gray-500 font-bold">
                <tr>
                    <th class="p-4">ID</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Date Added</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($contacts as $contact)
                <tr class="hover:bg-gray-50">
                    <td class="p-4 text-gray-500">#{{ $contact->id }}</td>
                    <td class="p-4 font-medium">{{ $contact->email }}</td>
                    <td class="p-4 text-gray-500">{{ $contact->created_at }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection