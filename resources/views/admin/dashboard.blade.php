@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

{{-- ── Stat cards ──────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">

    {{-- Total events --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="h-11 w-11 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ number_format($totalEvents) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Eventi</p>
        </div>
    </div>

    {{-- Archived photos --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="h-11 w-11 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ number_format($totalPhotos) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Foto archiviate</p>
        </div>
    </div>

    {{-- Downloads --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="h-11 w-11 rounded-xl bg-sky-50 flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ number_format($totalDownloads) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Download foto</p>
        </div>
    </div>

    {{-- Pending reports --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="h-11 w-11 rounded-xl {{ $totalPendingReports > 0 ? 'bg-red-50' : 'bg-gray-50' }} flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $totalPendingReports > 0 ? 'text-red-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold {{ $totalPendingReports > 0 ? 'text-red-600' : 'text-gray-900' }} tabular-nums">
                {{ number_format($totalPendingReports) }}
            </p>
            <p class="text-xs text-gray-500 mt-0.5">Segnalazioni pendenti</p>
        </div>
    </div>

</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- ── Recent events ────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-800">Ultimi eventi creati</h2>
            <a href="{{ route('admin.events.index') }}"
               class="text-xs text-indigo-600 hover:text-indigo-800 font-medium hover:underline">
                Vedi tutti →
            </a>
        </div>

        @if($recentEvents->isEmpty())
            <div class="text-center py-12 text-gray-400">
                <p class="text-sm">Nessun evento ancora.</p>
                <a href="{{ route('admin.events.create') }}" class="mt-2 inline-block text-sm text-indigo-600 hover:underline">
                    Crea il primo →
                </a>
            </div>
        @else
            <ul class="divide-y divide-gray-50">
                @foreach($recentEvents as $event)
                    @php $thumb = $event->getFirstMediaUrl('gallery', 'thumb'); @endphp
                    <li class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/60 transition-colors">

                        @if($thumb)
                            <img src="{{ $thumb }}" alt="" class="h-10 w-10 rounded-lg object-cover flex-shrink-0 border border-gray-100">
                        @else
                            <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $event->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $event->event_date->format('d/m/Y') }}</p>
                        </div>

                        @if($event->status === 'published')
                            <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full flex-shrink-0">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Pubblicato
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full flex-shrink-0">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span> Bozza
                            </span>
                        @endif

                        <a href="{{ route('admin.events.edit', $event) }}"
                           class="text-gray-400 hover:text-indigo-600 transition-colors flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- ── Top events by views ──────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-800">Serate più viste</h2>
        </div>

        @if($topEvents->isEmpty())
            <div class="text-center py-12 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <p class="text-sm">Nessuna visualizzazione ancora.</p>
            </div>
        @else
            <ul class="divide-y divide-gray-50">
                @foreach($topEvents as $i => $event)
                    <li class="flex items-center gap-4 px-5 py-3.5">

                        {{-- Rank --}}
                        <span class="text-sm font-bold tabular-nums w-5 text-center
                            {{ $i === 0 ? 'text-amber-500' : ($i === 1 ? 'text-gray-400' : ($i === 2 ? 'text-amber-700' : 'text-gray-300')) }}">
                            {{ $i + 1 }}
                        </span>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $event->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $event->event_date->format('d/m/Y') }}</p>
                        </div>

                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-bold text-gray-900 tabular-nums">{{ number_format($event->views_count) }}</p>
                            <p class="text-xs text-gray-400">visualizzazioni</p>
                        </div>

                        <a href="{{ route('admin.events.edit', $event) }}"
                           class="text-gray-400 hover:text-indigo-600 transition-colors flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

</div>

@endsection
