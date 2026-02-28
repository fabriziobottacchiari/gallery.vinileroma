@extends('layouts.public')

@section('title', 'Le mie foto — ' . config('app.name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-zinc-100 tracking-tight">Le mie foto</h1>
        <a href="{{ route('public.events.index') }}"
           class="text-sm text-zinc-500 hover:text-zinc-300 transition-colors">
            ← Tutti gli eventi
        </a>
    </div>

    @if(empty($photos))
        <div class="text-center py-24 text-zinc-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <p class="text-sm">Non hai ancora salvato nessuna foto.</p>
            <a href="{{ route('public.events.index') }}"
               class="inline-block mt-4 px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white text-sm rounded-lg transition-colors">
                Esplora gli eventi
            </a>
        </div>
    @else
        <div x-data="favoritesPage()" class="columns-2 sm:columns-3 lg:columns-4 gap-3">
            @foreach($photos as $photo)
                <div class="break-inside-avoid mb-3"
                     x-show="!removed[{{ $photo['id'] }}]"
                     x-transition:leave="transition-opacity duration-300"
                     x-transition:leave-end="opacity-0">

                    <div class="group relative overflow-hidden rounded-xl bg-zinc-800">

                        {{-- Photo links to event lightbox --}}
                        <a href="{{ route('public.events.show', $photo['event']->publicRouteParams()) }}?foto={{ $photo['id'] }}">
                            <img src="{{ $photo['thumb'] }}"
                                 alt="{{ $photo['event']->title }}"
                                 loading="lazy"
                                 class="w-full object-cover transition duration-500 group-hover:scale-105 group-hover:brightness-110">
                        </a>

                        {{-- Event overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent pointer-events-none"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-3 pointer-events-none">
                            <p class="text-[10px] text-zinc-400">{{ $photo['event']->event_date->format('d/m/Y') }}</p>
                            <p class="text-xs font-semibold text-white leading-tight mt-0.5 truncate">{{ $photo['event']->title }}</p>
                        </div>

                        {{-- Remove from favourites button (visible on hover) --}}
                        <button @click="removeFavorite({{ $photo['id'] }}, '{{ $photo['favoriteUrl'] }}')"
                                title="Rimuovi dai preferiti"
                                class="absolute top-2 right-2 h-8 w-8 flex items-center justify-center rounded-full bg-black/60 hover:bg-red-600/80 text-red-400 hover:text-white transition-all duration-150 opacity-0 group-hover:opacity-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        @if($favorites->hasPages())
            <div class="mt-10">
                {{ $favorites->links() }}
            </div>
        @endif
    @endif

</div>
@endsection

@section('scripts')
<script>
function favoritesPage() {
    return {
        removed: {},

        async removeFavorite(mediaId, url) {
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                if (res.ok) {
                    this.removed[mediaId] = true;
                }
            } catch (_) {
                // silent — photo stays visible
            }
        },
    };
}
</script>
@endsection
