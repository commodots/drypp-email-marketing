@extends('layouts.app')
@section('title', 'Manage Packages')
@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        
        <button x-data @click="$dispatch('open-modal')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">
            + Create New Package
        </button>
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
                    <td class="p-4 text-sm font-mono">₦{{ number_format($package->price_ngn) }}</td>
                    <td class="p-4 text-right space-x-2">
                        <button class="text-blue-600 text-sm hover:underline">Edit</button>
                        <form action="{{ route('admin.packages.destroy', $package) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-sm hover:underline" onclick="return confirm('Delete this package?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>


    <div x-data="{ show: false }" @open-modal.window="show = true" x-show="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6" @click.away="show = false">
            <h2 class="text-xl font-bold mb-4">Add New Package</h2>
            <form action="{{ route('admin.packages.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" class="w-full mt-1 border-gray-300 rounded-md shadow-sm" placeholder="e.g. Premium Plan" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Type</label>
                    <select name="type" class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                        <option value="cold_email">Cold Email</option>
                        <option value="email_marketing">Email Marketing</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email Limit</label>
                    <input type="number" name="email_limit" class="w-full mt-1 border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Price (NGN)</label>
                    <input type="number" name="price_ngn" class="w-full mt-1 border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="show = false" class="text-gray-600 px-4 py-2">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold">Create Package</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection