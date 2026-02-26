@extends('layouts.public')

@section('title', 'Archivio eventi — ' . config('app.name'))

@push('head')
    <meta property="og:type"        content="website">
    <meta property="og:title"       content="Archivio eventi — {{ config('app.name') }}">
    <meta property="og:description" content="Galleria fotografica ufficiale delle serate.">
    <meta property="og:url"         content="{{ url('/') }}">
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">

    <h1 class="text-2xl font-bold text-zinc-100 mb-8 tracking-tight">Archivio eventi</h1>

    @if($events->isEmpty())
        <div class="text-center py-24 text-zinc-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm">Nessun evento disponibile.</p>
        </div>
    @else
        <div class="columns-2 sm:columns-3 lg:columns-4 gap-3">
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

                        {{-- Overlay gradient --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>

                        {{-- Event info --}}
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

        @if($events->hasPages())
            <div class="mt-10">
                {{ $events->links() }}
            </div>
        @endif
    @endif

</div>
@endsection
