<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?: config('app.name', 'DealHunt') }}</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: radial-gradient(1200px 600px at 20% -10%, rgba(185, 28, 28, 0.35), transparent 55%),
                        radial-gradient(900px 500px at 100% 10%, rgba(127, 29, 29, 0.35), transparent 50%),
                        #111827;
            color: #f9fafb;
            min-height: 100vh;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-10">
        <a href="{{ route('home') }}" class="mb-8 text-center group">
            <div class="text-3xl font-bold tracking-wide text-white group-hover:text-red-300 transition">
                {{ config('app.name', 'DealHunt') }}
            </div>
            <div class="text-xs text-gray-400 mt-1">ofertas de jogos em tempo real</div>
        </a>

        <div class="w-full max-w-md">
            {{ $slot }}
        </div>

        <p class="mt-8 text-xs text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-gray-300 underline underline-offset-2">Voltar às ofertas</a>
        </p>
    </div>
</body>
</html>
