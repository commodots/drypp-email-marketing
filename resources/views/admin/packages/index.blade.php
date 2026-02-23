@extends('layouts.app')
@section('title', 'Manage Packages')
@section('content')

<div class="space-y-6">
    @if(session('success'))
        <div id="success-alert" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 font-bold border border-green-200 shadow-sm transition-all duration-500">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800">Subscription Packages</h2>
        <a href="{{ route('admin.packages.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
            + Create New Package
        </a>
    </div>

    {{-- Packages Table --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b text-xs uppercase text-gray-500 font-bold">
                <tr>
                    <th class="p-4">Package Name</th>
                    <th class="p-4">Type</th>
                    <th class="p-4">Email Limit</th>
                    <th class="p-4">Price (NGN)</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($packages as $package)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-4 font-semibold text-gray-900">{{ $package->name }}</td>
                    <td class="p-4 text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $package->type) }}</td>
                    <td class="p-4 text-sm">{{ number_format($package->email_limit) }}</td>
                    <td class="p-4 text-sm font-mono text-blue-600">₦{{ number_format($package->price_ngn) }}</td>
                    <td class="p-4 text-right space-x-2">
                        <a href="{{ route('admin.packages.edit', $package) }}" class="text-blue-600 text-sm font-bold hover:underline">Edit</a>
                        
                        <form action="{{ route('admin.packages.destroy', $package) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-sm font-bold hover:underline" onclick="return confirm('Delete this package?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    // Fades out the success alert automatically
    setTimeout(() => {
        let alert = document.getElementById('success-alert');
        if(alert) {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }
    }, 4000);
</script>
@endpush
@endsection