@extends('layouts.public')

@section('title', 'Archivio eventi — ' . config('app.name'))

@push('head')
    <meta property="og:type"        content="website">
    <meta property="og:title"       content="Archivio eventi — {{ config('app.name') }}">
    <meta property="og:description" content="Galleria fotografica ufficiale delle serate.">
    <meta property="og:url"         content="{{ url('/') }}">
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-10"
     x-data="{
        view: localStorage.getItem('eventsView') || 'grid',
        setView(v) { this.view = v; localStorage.setItem('eventsView', v); }
     }">

    {{-- Header row --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-zinc-100 tracking-tight">Archivio eventi</h1>

        {{-- View toggle --}}
        @if(!$events->isEmpty())
        <div class="flex items-center gap-1 bg-zinc-900 border border-zinc-800 rounded-lg p-1">
            {{-- Grid --}}
            <button @click="setView('grid')"
                    :class="view === 'grid'
                        ? 'bg-zinc-700 text-white shadow-sm'
                        : 'text-zinc-500 hover:text-zinc-300'"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span class="hidden sm:inline">Griglia</span>
            </button>
            {{-- List --}}
            <button @click="setView('list')"
                    :class="view === 'list'
                        ? 'bg-zinc-700 text-white shadow-sm'
                        : 'text-zinc-500 hover:text-zinc-300'"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <span class="hidden sm:inline">Elenco</span>
            </button>
        </div>
        @endif
    </div>

    {{-- Search / filter bar --}}
    <form method="GET" action="{{ route('public.events.index') }}" class="mb-8">
        <div class="flex flex-col sm:flex-row gap-2">
            {{-- Title search --}}
            <div class="relative flex-1">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-500 pointer-events-none"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
                <input type="text"
                       name="q"
                       value="{{ $q }}"
                       placeholder="Cerca per titolo…"
                       class="w-full pl-9 pr-4 py-2 bg-zinc-900 border border-zinc-800 rounded-lg text-sm text-zinc-100 placeholder-zinc-600 focus:outline-none focus:border-zinc-600 focus:ring-0">
            </div>

            {{-- Year filter --}}
            @if($availableYears->isNotEmpty())
            <select name="year"
                    class="bg-zinc-900 border border-zinc-800 rounded-lg text-sm text-zinc-100 px-3 py-2 focus:outline-none focus:border-zinc-600 min-w-[120px]">
                <option value="">Tutti gli anni</option>
                @foreach($availableYears as $y)
                    <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                @endforeach
            </select>
            @endif

            <button type="submit"
                    class="px-4 py-2 bg-zinc-700 hover:bg-zinc-600 text-white text-sm font-medium rounded-lg transition-colors duration-150">
                Filtra
            </button>

            @if($q !== '' || $year !== '')
                <a href="{{ route('public.events.index') }}"
                   class="px-4 py-2 bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-zinc-400 hover:text-zinc-200 text-sm font-medium rounded-lg transition-colors duration-150">
                    Reset
                </a>
            @endif
        </div>
    </form>

    @if($events->isEmpty())
        <div class="text-center py-24 text-zinc-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            @if($q !== '' || $year !== '')
            <p class="text-sm">Nessun evento trovato per questa ricerca.</p>
            <a href="{{ route('public.events.index') }}" class="inline-block mt-3 text-xs text-zinc-500 hover:text-zinc-300 underline underline-offset-2">Rimuovi i filtri</a>
        @else
            <p class="text-sm">Nessun evento disponibile.</p>
        @endif
        </div>
    @else

        {{-- ── GRID VIEW ──────────────────────────────────────────────────── --}}
        <div x-show="view === 'grid'" x-cloak class="columns-2 sm:columns-3 lg:columns-4 gap-3">
            @foreach($events as $event)
                @php $coverThumb = $coverUrls[$event->id] ?? null; @endphp
                <div class="break-inside-avoid mb-3">
                    <a href="{{ route('public.events.show', $event->publicRouteParams()) }}"
                       class="group relative block overflow-hidden rounded-xl bg-zinc-800">

                        @if($coverThumb)
                            <img src="{{ $coverThumb }}"
                                 alt="{{ $event->title }}"
                                 loading="lazy"
                                 class="w-full object-cover transition duration-500 group-hover:scale-105 group-hover:brightness-110">
                        @else
                            <div class="aspect-[4/3] bg-zinc-800 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif

                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-3">
                            <p class="text-[11px] text-zinc-300 font-medium">
                                {{ $event->event_date->format('d/m/Y') }}
                            </p>
                            <p class="text-sm font-semibold text-white leading-tight mt-0.5">
                                {{ $event->title }}
                            </p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        {{-- ── LIST VIEW ──────────────────────────────────────────────────── --}}
        <div x-show="view === 'list'" x-cloak class="divide-y divide-zinc-800/70">
            @foreach($events as $event)
                @php
                    $photoCount = $event->photoUploads()->where('status', 'completed')->count();
                @endphp
                <a href="{{ route('public.events.show', $event->publicRouteParams()) }}"
                   class="group flex items-center gap-4 py-3.5 hover:bg-zinc-900/60 -mx-3 px-3 rounded-xl transition-colors duration-150">

                    {{-- Date badge --}}
                    <div class="shrink-0 w-12 text-center">
                        <p class="text-lg font-bold text-white leading-none">
                            {{ $event->event_date->format('d') }}
                        </p>
                        <p class="text-[10px] uppercase tracking-widest text-zinc-500 mt-0.5">
                            {{ $event->event_date->translatedFormat('M') }}
                        </p>
                        <p class="text-[10px] text-zinc-600 mt-0.5">
                            {{ $event->event_date->format('Y') }}
                        </p>
                    </div>

                    {{-- Divider --}}
                    <div class="shrink-0 w-px h-10 bg-zinc-800"></div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-zinc-100 group-hover:text-white transition-colors truncate">
                            {{ $event->title }}
                        </p>
                        @if($event->description)
                            <p class="text-xs text-zinc-500 mt-0.5 truncate">
                                {{ $event->description }}
                            </p>
                        @endif
                    </div>

                    {{-- Photo count --}}
                    @if($photoCount > 0)
                        <div class="shrink-0 flex items-center gap-1.5 text-zinc-500 text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ $photoCount }}</span>
                        </div>
                    @endif

                    {{-- Arrow --}}
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4 text-zinc-700 group-hover:text-zinc-400 transition-colors shrink-0"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endforeach
        </div>

        @if($events->hasPages())
            <div class="mt-10">
                {{ $events->links() }}
            </div>
        @endif
    @endif

</div>
@endsection
