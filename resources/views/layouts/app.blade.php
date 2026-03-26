<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100">

<div class="flex min-h-screen">
    {{-- Sidebar --}}
    @include('partials.sidebar')

    {{-- Main Content --}}
    <div class="flex-1 lg:ml-0">
        {{-- Topbar --}}
        <header class="flex justify-between px-4 md:px-6 py-4 bg-white shadow">
            <div class="flex items-center">
                <button id="sidebarToggle" class="lg:hidden mr-4 text-gray-600 hover:text-gray-800">☰</button>
                <h1 class="text-xl font-semibold">@yield('title')</h1>
            </div>
            <div>
                {{ auth()->user()->email }}
            </div>
        </header>

        {{-- Page Content --}}
        <main class="p-4 md:p-6">
        @yield('content')

        @stack('scripts')

<script>
document.getElementById('sidebarToggle').addEventListener('click', function() {
    const sidebar = document.querySelector('aside');
    sidebar.classList.toggle('hidden');
});
</script>
    </main>
    </div>
</div>
</body>
</html>
