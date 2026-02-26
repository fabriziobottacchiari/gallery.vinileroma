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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-14 flex items-center">
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
    </div>
</header>

<main>
    @yield('content')
</main>

@yield('scripts')

{{-- PWA Service Worker --}}
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
</script>
</body>
</html>
