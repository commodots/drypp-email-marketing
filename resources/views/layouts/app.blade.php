<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-100">

<div class="flex min-h-screen">
    {{-- Sidebar --}}
    @include('partials.sidebar')

    {{-- Main Content --}}
    <div class="flex-1">
        {{-- Topbar --}}
        <header class="flex justify-between px-6 py-4 bg-white shadow">
            <h1 class="text-xl font-semibold">@yield('title')</h1>
            <div>
                {{ auth()->user()->email }}
            </div>
        </header>

        {{-- Page Content --}}
        <main class="p-6">
        @yield('content')
    </main>
    </div>
</div>
</body>
</html>
