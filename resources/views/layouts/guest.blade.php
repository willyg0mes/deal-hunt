<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'DealHunt'))</title>
    <meta name="robots" content="noindex,nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-10">
        <a href="{{ route('home') }}" class="mb-6 text-2xl font-bold text-red-400">{{ config('app.name', 'DealHunt') }}</a>
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </div>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
