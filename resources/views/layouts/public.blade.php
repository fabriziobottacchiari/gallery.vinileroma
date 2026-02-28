<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#09090b">
    <title>@yield('title', config('app.name'))</title>

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    {{-- Open Graph base (pages override with @push('head')) --}}
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="it_IT">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inject('_settings', 'App\Services\SystemSettingsService')
    @if($_css = $_settings->get('custom_css', ''))
        <style>{!! $_css !!}</style>
    @endif
    <style>[x-cloak]{display:none!important}</style>
    @stack('head')
</head>
<body class="font-sans antialiased bg-zinc-950 text-white min-h-screen">

<header class="sticky top-0 z-30 bg-zinc-950/90 backdrop-blur-md border-b border-zinc-800/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
        @php $logo = \App\Models\BrandingSettings::instance()->getFirstMedia('logo'); @endphp
        @if($logo)
            <a href="{{ route('public.events.index') }}">
                <img src="{{ $logo->getUrl() }}" alt="{{ config('app.name') }}" class="h-8 w-auto object-contain">
            </a>
        @else
            <a href="{{ route('public.events.index') }}"
               class="text-base font-bold tracking-widest uppercase text-white hover:text-zinc-300 transition-colors">
                {{ config('app.name') }}
            </a>
        @endif

        {{-- Gallery user auth nav --}}
        <nav class="flex items-center gap-3 text-sm">
            @auth('gallery')
                <a href="{{ route('public.favorites.index') }}"
                   class="flex items-center gap-1.5 text-zinc-400 hover:text-white transition-colors"
                   title="Le mie foto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span class="text-sm">Le mie foto</span>
                </a>
                <span class="hidden sm:block text-zinc-400 text-sm">
                    {{ auth('gallery')->user()->first_name }}
                </span>
                <form method="POST" action="{{ route('gallery.logout') }}">
                    @csrf
                    <button type="submit"
                            class="text-zinc-400 hover:text-white transition-colors text-sm">
                        Esci
                    </button>
                </form>
            @else
                <a href="{{ route('gallery.login') }}"
                   class="text-zinc-400 hover:text-white transition-colors">
                    Accedi
                </a>
                <a href="{{ route('gallery.register') }}"
                   class="bg-indigo-600 hover:bg-indigo-500 text-white px-3.5 py-1.5 rounded-lg font-medium transition-colors text-xs">
                    Registrati
                </a>
            @endauth
        </nav>
    </div>
</header>

<main>
    @yield('content')
</main>

<footer class="border-t border-zinc-800/60 mt-auto py-6">
    <p class="text-center text-xs text-zinc-600">
        Developed by <a href="https://www.maioralabs.it/" target="_blank" rel="noopener noreferrer"
                        class="text-zinc-500 hover:text-zinc-300 transition-colors">Maiora Labs</a>
    </p>
</footer>

@yield('scripts')

{{-- PWA Service Worker --}}
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
</script>
</body>
</html>
