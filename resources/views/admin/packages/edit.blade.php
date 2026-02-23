@extends('layouts.app')
@section('title', 'Edit Package: ' . $package->name)
@section('content')

<div class="max-w-md mx-auto py-8">
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-xl font-bold mb-6 text-gray-800">Edit Package</h2>

        <form action="{{ route('admin.packages.update', $package) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700">Package Name</label>
                <input type="text" name="name" value="{{ old('name', $package->name) }}" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Package Type</label>
                <select name="type" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="cold_email" {{ old('type', $package->type) == 'cold_email' ? 'selected' : '' }}>Cold Email</option>
                    <option value="email_marketing" {{ old('type', $package->type) == 'email_marketing' ? 'selected' : '' }}>Email Marketing</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email Limit</label>
                <input type="text" name="email_limit" value="{{ old('email_limit', $package->email_limit) }}" 
                       class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 number-format" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Price (NGN)</label>
                <div class="relative mt-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">₦</span>
                    <input type="text" name="price_ngn" value="{{ old('price_ngn', $package->price_ngn) }}" 
                           class="w-full pl-7 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 number-format" required>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t">
                <a href="{{ route('admin.packages.index') }}" class="text-gray-500 px-4 py-2 text-sm font-bold hover:text-gray-800 transition">Cancel</a>
                <button type="submit" data-loading-text="Updating package..." class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-md transition">
                    Update Package
                </button>
            </div>
        </form>
    </div>
</div>
@endsection