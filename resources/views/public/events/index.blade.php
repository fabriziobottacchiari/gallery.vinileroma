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

    {{-- ── Filter card ───────────────────────────────────────────────── --}}
    @php $hasFilters = $q !== '' || $year !== '' || $month !== '' || $day !== ''; @endphp
    <div class="mb-8 bg-zinc-900/60 border border-zinc-800 rounded-2xl p-4 sm:p-5">

        {{-- Card header --}}
        <div class="flex items-center gap-2 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Filtra eventi</span>
            @if($hasFilters)
                <span class="ml-auto">
                    <a href="{{ route('public.events.index') }}"
                       class="inline-flex items-center gap-1 text-xs text-zinc-500 hover:text-zinc-200 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Azzera filtri
                    </a>
                </span>
            @endif
        </div>

        <form method="GET" action="{{ route('public.events.index') }}">
            {{-- Row 1: search --}}
            <div class="relative mb-3">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-500 pointer-events-none"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
                <input type="text"
                       name="q"
                       value="{{ $q }}"
                       placeholder="Cerca per titolo dell'evento…"
                       class="w-full pl-10 pr-4 py-2.5 bg-zinc-800/80 border border-zinc-700/60 rounded-xl text-sm text-zinc-100 placeholder-zinc-600 focus:outline-none focus:border-zinc-500 transition-colors">
            </div>

            {{-- Row 2: date selects + submit --}}
            <div class="flex flex-wrap gap-2">

                {{-- Year --}}
                @if($availableYears->isNotEmpty())
                <div class="flex flex-col gap-1 min-w-[100px]">
                    <label class="text-[10px] font-semibold text-zinc-600 uppercase tracking-wider px-1">Anno</label>
                    <select name="year"
                            class="bg-zinc-800/80 border border-zinc-700/60 rounded-xl text-sm text-zinc-100 px-3 py-2 focus:outline-none focus:border-zinc-500 transition-colors cursor-pointer">
                        <option value="">Tutti</option>
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Month --}}
                @if($availableMonths->isNotEmpty())
                <div class="flex flex-col gap-1 min-w-[130px]">
                    <label class="text-[10px] font-semibold text-zinc-600 uppercase tracking-wider px-1">Mese</label>
                    <select name="month"
                            class="bg-zinc-800/80 border border-zinc-700/60 rounded-xl text-sm text-zinc-100 px-3 py-2 focus:outline-none focus:border-zinc-500 transition-colors cursor-pointer">
                        <option value="">Tutti</option>
                        @foreach($availableMonths as $m)
                            <option value="{{ $m }}" @selected($month == $m)>{{ $monthNames[$m] }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Day --}}
                @if($availableDays->isNotEmpty())
                <div class="flex flex-col gap-1 min-w-[80px]">
                    <label class="text-[10px] font-semibold text-zinc-600 uppercase tracking-wider px-1">Giorno</label>
                    <select name="day"
                            class="bg-zinc-800/80 border border-zinc-700/60 rounded-xl text-sm text-zinc-100 px-3 py-2 focus:outline-none focus:border-zinc-500 transition-colors cursor-pointer">
                        <option value="">Tutti</option>
                        @foreach($availableDays as $d)
                            <option value="{{ $d }}" @selected($day == $d)>{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Submit --}}
                <div class="flex flex-col gap-1 justify-end">
                    <label class="text-[10px] text-transparent select-none">_</label>
                    <button type="submit"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition-colors duration-150 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                        </svg>
                        Cerca
                    </button>
                </div>
            </div>

            {{-- Active filter pills --}}
            @if($hasFilters)
            <div class="flex flex-wrap gap-1.5 mt-3 pt-3 border-t border-zinc-800">
                <span class="text-[10px] text-zinc-600 self-center mr-1">Filtri attivi:</span>
                @if($q !== '')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-900/40 border border-indigo-700/50 rounded-full text-xs text-indigo-300">
                        "{{ $q }}"
                    </span>
                @endif
                @if($year !== '')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-zinc-800 border border-zinc-700 rounded-full text-xs text-zinc-300">
                        {{ $year }}
                    </span>
                @endif
                @if($month !== '')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-zinc-800 border border-zinc-700 rounded-full text-xs text-zinc-300">
                        {{ $monthNames[(int)$month] ?? $month }}
                    </span>
                @endif
                @if($day !== '')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-zinc-800 border border-zinc-700 rounded-full text-xs text-zinc-300">
                        Giorno {{ $day }}
                    </span>
                @endif
            </div>
            @endif
        </form>
    </div>

    @if($events->isEmpty())
        <div class="text-center py-24 text-zinc-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            @if($hasFilters)
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
