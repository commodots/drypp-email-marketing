<aside class="w-64 min-h-screen px-4 py-6 text-white bg-gray-900">
    <div class="mb-8 text-2xl font-bold">
        Drypp Email
    </div>

    <nav class="space-y-3">
        <a href="{{ route('dashboard') }}" class="block hover:text-blue-400">Dashboard</a>
        <a href="{{ route('campaigns.index') }}" class="block hover:text-blue-400">Campaigns</a>
        <a href="{{ route('contacts.index') }}" class="block hover:text-blue-400">Contacts</a>
        <a href="{{ route('leads.index') }}" class="block hover:text-blue-400">Buy Leads</a>
        <a href="{{ route('billing.index') }}" class="block hover:text-blue-400">Billing & Packages</a>
        <a href="{{ route('reports.index') }}" class="block hover:text-blue-400">Reports</a>
        <a href="{{ route('settings.index') }}" class="block hover:text-blue-400">Settings</a>

        @if(auth()->user()->role === 'admin')
            <hr class="my-4 border-gray-700">
            <a href="{{ route('admin.dashboard') }}" class="block text-red-400">Admin Dashboard</a>
        @endif
    </nav>
</aside>