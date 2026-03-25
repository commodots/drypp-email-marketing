@extends('layouts.app')
@section('title', 'Payments & Subscriptions')
@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-2 px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
            </span>
            Gateway: {{ $gatewayStatus }}
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b text-xs uppercase text-gray-500 font-bold">
                <tr>
                    <th class="p-4">User</th>
                    <th class="p-4">Current Plan</th>
                    <th class="p-4">Expiry Date</th>
                    <th class="p-4 text-right">Manual Override</th>
                </tr>
            </thead>
            <tbody class="divide-y text-sm">
                @foreach($users as $user)
                <tr>
                    <td class="p-4 font-medium">{{ $user->name }}</td>
                    <td class="p-4">
                        @if($user->subscription?->package)
                            <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">
                                {{ $user->subscription->package->name }}
                            </span>
                        @else
                            <span class="text-gray-400 italic">No Active Plan</span>
                        @endif
                    </td>
                    <td class="p-4 text-gray-600">
                        {{ $user->subscription?->expires_at?->format('M d, Y') ?? 'N/A' }}
                    </td>
                    <td class="p-4 text-right">
                        <form action="{{ route('admin.payments.override', $user) }}" method="POST" class="flex justify-end gap-2">
                            @csrf
                            <select name="package_id" class="text-xs border-gray-300 rounded px-2 py-1">
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}">{{ $package->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="bg-gray-800 text-white px-3 py-1 rounded text-xs hover:bg-black transition">
                                Grant Access
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection