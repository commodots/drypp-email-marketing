@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<div class="space-y-6">
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-6 bg-white rounded shadow-sm border-t-4 border-red-500">
            <h3 class="text-gray-500 font-medium">Total Users</h3>
            <p class="text-3xl font-bold">{{ $totalUsers }}</p>
        </div>
        <div class="p-6 bg-white rounded shadow-sm border-t-4 border-blue-500">
            <h3 class="text-gray-500 font-medium">Active Campaigns</h3>
            <p class="text-3xl font-bold">{{ $activeCampaigns }}</p>
        </div>
        <div class="p-6 bg-white rounded shadow-sm border-t-4 border-yellow-500">
            <h3 class="text-gray-500 font-medium">Emails Sent Today</h3>
            <p class="text-3xl font-bold">{{ $activeCampaigns }}</p>
        </div>
        <div class="p-6 bg-white rounded shadow-sm border-t-4 border-green-500">
            <h3 class="text-gray-500 font-medium">SMTP Health</h3>
            <p class="text-3xl font-bold">{{ $smtpHealth }} Online</p>
        </div>
    </div>
</div>
@endsection