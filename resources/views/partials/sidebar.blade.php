<aside id="sidebar" class="hidden fixed inset-y-0 left-0 z-50 w-64 px-4 py-6 text-white bg-gray-900 lg:static lg:block lg:min-h-screen">
    
    <div class="flex items-center justify-between mb-8">
        <div class="mb-8 text-2xl font-bold">
            Drypp
        </div>
        <button id="closeSidebar" class="lg:hidden text-gray-400 hover:text-white text-2xl">
            ✕
        </button>
    </div>

    <nav class="space-y-3">
        @if (auth()->user()->role === 'user')
            <a href="/dashboard" class="block hover:text-blue-400">Dashboard</a>
            <div>
                <a href="{{ route('campaigns.index') }}" class="block hover:text-blue-400">Campaigns</a>

                <a href="{{ route('campaigns.create') }}" class="ml-4 text-sm text-neutral-300 hover:text-blue-400">Create
                    Campaign</a>
            </div>

            <a href="/contacts" class="block hover:text-blue-400">Contacts</a>
            <a href="/reports" class="block hover:text-blue-400">Reports</a>
            <a href="{{ route('automation.index') }}" class="block hover:text-blue-400">Automation</a>
            <a href="/billing" class="block hover:text-blue-400">Billing</a>
        @endif
        {{-- ADMIN --}}
        @if (auth()->user()->role === 'admin')
            <div class="pb-2 text-xs font-semibold text-gray-400 uppercase">Admin Settings</div>
            <a href="{{ route('admin.dashboard') }}" class="block hover:text-blue-400">
                Dashboard
            </a>
            <a href="{{ route('admin.campaigns.index') }}" class="block hover:text-blue-400">
                Campaigns
            </a>
            <div>
                <a href="{{ route('admin.packages.index') }}" class="block hover:text-blue-400">
                    Manage Packages
                </a>
                <a href="{{ route('admin.packages.create') }}"
                    class="ml-4 text-sm text-neutral-300 hover:text-blue-400">Create Package</a>
            </div>

            <a href="{{ route('admin.smtps.index') }}" class="block hover:text-blue-400">
                SMTP Management
            </a>

            <a href="{{ route('admin.payments.index') }}" class="block hover:text-blue-400">
                Payments
            </a>
        @endif
        <div class="block text-white hover:text-red-700">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                    Logout
                </button>
            </form>
        </div>
    </nav>
</aside>