@extends('layouts.app')
@section('title', 'SMTP Management')
@section('content')

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">SMTP Servers</h2>
    </div>

    {{-- Add Server Form --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <h3 class="font-bold mb-4">Add New Server</h3>
        <form action="{{ route('admin.smtps.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <input type="text" name="name" placeholder="Provider (e.g. Mailgun)" class="border-gray-300 rounded-lg" required>
            <input type="text" name="host" placeholder="Host IP / URL" class="border-gray-300 rounded-lg" required>
            <input type="text" name="port" placeholder="Port" class="border-gray-300 rounded-lg" required>
            <input type="text" name="username" placeholder="Username" class="border-gray-300 rounded-lg" required>
            <input type="password" name="password" placeholder="Password" class="border-gray-300 rounded-lg" required>
            <input type="number" name="daily_limit" placeholder="Daily Limit" class="border-gray-300 rounded-lg" required>
            <button class="bg-gray-800 text-white px-4 py-2 rounded-lg font-bold hover:bg-black">Add Server</button>
        </form>
    </div>

    {{-- Server List --}}
    <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b text-xs uppercase text-gray-500 font-bold">
                <tr>
                    <th class="p-4">Name</th>
                    <th class="p-4">Host</th>
                    <th class="p-4">Limit</th>
                    <th class="p-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($servers as $server)
                <tr>
                    <td class="p-4 font-bold">{{ $server->name }}</td>
                    <td class="p-4 font-mono text-sm text-gray-600">{{ $server->host }}</td>
                    <td class="p-4">{{ number_format($server->daily_limit) }}</td>
                    <td class="p-4 text-right">
                        <form action="{{ route('admin.smtps.destroy', $server) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="text-red-600 text-xs hover:underline">Remove</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="p-4 text-center text-gray-500">No servers added yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection