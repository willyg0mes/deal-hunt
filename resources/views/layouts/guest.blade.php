<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?: config('app.name', 'DealHunt') }}</title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="description" content="Entre ou crie sua conta no DealHunt para wishlist e alertas de ofertas de jogos.">
    @include('partials.brand-head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Montserrat', system-ui, sans-serif;
            background:
                radial-gradient(1100px 520px at 12% -8%, rgba(220, 38, 38, 0.28), transparent 55%),
                radial-gradient(900px 480px at 100% 0%, rgba(127, 29, 29, 0.32), transparent 48%),
                radial-gradient(700px 400px at 50% 110%, rgba(185, 28, 28, 0.12), transparent 50%),
                #0b1220;
            color: #f9fafb;
            min-height: 100vh;
        }
        [x-cloak] { display: none !important; }
        .auth-card {
            background: linear-gradient(180deg, rgba(17, 24, 39, 0.92), rgba(2, 6, 23, 0.88));
            border: 1px solid rgba(220, 38, 38, 0.35);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.04);
        }
        .auth-input {
            background: #0f172a;
            border-color: rgba(185, 28, 28, 0.55);
            color: #f8fafc;
        }
        .auth-input:focus {
            border-color: #ef4444;
            --tw-ring-color: rgba(239, 68, 68, 0.45);
        }
        .auth-tab {
            transition: color .2s ease, background .2s ease, box-shadow .2s ease;
        }
        .auth-tab-active {
            background: rgba(185, 28, 28, 0.22);
            color: #fecaca;
            box-shadow: inset 0 0 0 1px rgba(248, 113, 113, 0.35);
        }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-10">
        <a href="{{ route('home') }}" class="mb-8 text-center group inline-flex flex-col items-center gap-3">
            <span class="relative inline-flex items-center justify-center">
                <span class="absolute inset-0 rounded-2xl bg-red-600/20 blur-xl scale-110 group-hover:bg-red-500/25 transition"></span>
                <img
                    src="{{ asset('images/brand/icon.png') }}"
                    alt="DealHunt"
                    width="72"
                    height="72"
                    class="relative h-16 w-16 sm:h-[4.5rem] sm:w-[4.5rem] rounded-2xl shadow-lg shadow-black/40 ring-1 ring-red-500/40 object-cover"
                />
            </span>
            <span>
                <span class="block text-3xl font-extrabold tracking-wide text-white group-hover:text-red-200 transition">
                    Deal<span class="text-red-500">Hunt</span>
                </span>
                <span class="block text-xs text-slate-400 mt-1 tracking-wide">ofertas de jogos em tempo real</span>
            </span>
        </a>

        <div class="w-full max-w-md">
            {{ $slot }}
        </div>

        <p class="mt-8 text-xs text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-slate-300 underline underline-offset-2">Voltar às ofertas</a>
        </p>
    </div>
    <script src="{{ asset('js/pwa-install.js') }}" defer></script>
</body>
</html>
