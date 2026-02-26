@extends('layouts.public')

@section('title', $event->title . ' — ' . config('app.name'))

@push('head')
    @if($event->is_private)
        <meta name="robots" content="noindex, nofollow">
    @endif
    <meta property="og:type"        content="article">
    <meta property="og:title"       content="{{ $event->title }} — {{ config('app.name') }}">
    <meta property="og:description" content="{{ $event->description ?: 'Galleria fotografica del ' . $event->event_date->format('d/m/Y') }}">
    <meta property="og:url"         content="{{ url()->current() }}">
    @if($ogImageUrl)
        <meta property="og:image"      content="{{ $ogImageUrl }}">
        <meta name="twitter:image"     content="{{ $ogImageUrl }}">
    @endif
@endpush

@section('content')
<div x-data="gallery({{ Js::from($photos) }})"
     x-init="init()"
     @keydown.escape.window="closeLightbox()"
     @keydown.arrow-left.window="if(lightbox.open) prev()"
     @keydown.arrow-right.window="if(lightbox.open) next()">

    {{-- ── Event header ──────────────────────────────────────────────────── --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-8 pb-6">
        <p class="text-sm text-zinc-400 font-medium">{{ $event->event_date->format('d/m/Y') }}</p>
        <h1 class="text-3xl sm:text-4xl font-bold mt-1 tracking-tight">{{ $event->title }}</h1>
        @if($event->description)
            <p class="mt-3 text-zinc-400 max-w-2xl leading-relaxed">{{ $event->description }}</p>
        @endif
        <p class="mt-3 text-xs text-zinc-600">{{ count($photos) }} {{ count($photos) === 1 ? 'foto' : 'foto' }}</p>
    </div>

    {{-- ── Masonry gallery ────────────────────────────────────────────────── --}}
    <div class="px-4 sm:px-6 max-w-7xl mx-auto pb-16">
        @if(count($photos) === 0)
            <div class="text-center py-24 text-zinc-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm">Nessuna foto disponibile.</p>
            </div>
        @else
            <div class="columns-2 md:columns-3 lg:columns-4 gap-2 sm:gap-3">
                @foreach($photos as $i => $photo)
                    <div class="break-inside-avoid mb-2 sm:mb-3">
                        <img src="{{ $photo['thumb'] }}"
                             alt="Foto {{ $i + 1 }}"
                             loading="lazy"
                             class="w-full rounded-lg cursor-pointer hover:opacity-80 transition-opacity duration-200 block"
                             @click="openPhoto({{ $i }})">
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Lightbox ────────────────────────────────────────────────────────── --}}
    <div x-show="lightbox.open"
         x-cloak
         class="fixed inset-0 z-50 bg-black/95 flex flex-col"
         @touchstart.passive="touchStart($event)"
         @touchend.passive="touchEnd($event)">

        {{-- Top bar --}}
        <div class="flex items-center justify-between px-4 pt-4 pb-2 flex-shrink-0">
            <span x-text="(lightbox.index + 1) + ' / ' + photos.length"
                  class="text-sm text-zinc-400 tabular-nums"></span>

            {{-- Close --}}
            <button @click="closeLightbox()"
                    class="h-11 w-11 flex items-center justify-center rounded-full bg-zinc-800 hover:bg-zinc-700 text-white transition-colors"
                    aria-label="Chiudi">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Image area --}}
        <div class="flex-1 flex items-center justify-center relative min-h-0 px-12 sm:px-16">

            {{-- Prev --}}
            <button @click="prev()"
                    class="absolute left-2 sm:left-4 h-11 w-11 flex items-center justify-center rounded-full bg-zinc-800/80 hover:bg-zinc-700 text-white transition-colors z-10"
                    aria-label="Precedente">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            {{-- Main image (uses preview — up to 1200px WebP) --}}
            <template x-if="lightbox.open && current">
                <img :src="current.preview"
                     :alt="'Foto ' + (lightbox.index + 1)"
                     class="max-w-full max-h-full w-auto h-auto object-contain rounded-md select-none"
                     draggable="false">
            </template>

            {{-- Next --}}
            <button @click="next()"
                    class="absolute right-2 sm:right-4 h-11 w-11 flex items-center justify-center rounded-full bg-zinc-800/80 hover:bg-zinc-700 text-white transition-colors z-10"
                    aria-label="Successiva">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        {{-- Bottom action bar --}}
        <div class="flex-shrink-0 px-4 py-4 flex items-center justify-center gap-3">

            {{-- Share --}}
            <button @click="share()"
                    class="relative flex items-center gap-2 px-4 py-2.5 rounded-full bg-zinc-800 hover:bg-zinc-700 text-sm font-medium text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                </svg>
                <span x-text="lightbox.shareSuccess ? 'Link copiato!' : 'Condividi'"></span>
            </button>

            {{-- Download (only when a downloadUrl exists) --}}
            <template x-if="current && current.downloadUrl">
                <a :href="current.downloadUrl"
                   class="flex items-center gap-2 px-4 py-2.5 rounded-full bg-zinc-800 hover:bg-zinc-700 text-sm font-medium text-zinc-300 hover:text-white transition-colors"
                   download>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Scarica
                </a>
            </template>

            {{-- Report (only when a reportUrl exists) --}}
            <template x-if="current && current.reportUrl">
                <button @click="lightbox.reporting = true; lightbox.reportSent = false"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-full bg-zinc-800 hover:bg-zinc-700 text-sm font-medium text-zinc-300 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                    </svg>
                    Segnala
                </button>
            </template>
        </div>

        {{-- ── Report modal (slide up from bottom) ──────────────────────── --}}
        <div x-show="lightbox.reporting"
             x-cloak
             class="absolute inset-0 z-10 bg-black/70 flex items-end sm:items-center justify-center p-4"
             @click.self="lightbox.reporting = false">

            <div class="bg-zinc-900 border border-zinc-700 rounded-2xl p-6 w-full max-w-md"
                 @click.stop>

                <h3 class="text-base font-semibold text-white mb-4">Segnala questa foto</h3>

                {{-- Success state --}}
                <div x-show="lightbox.reportSent" x-cloak class="py-6 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-zinc-300">Segnalazione inviata. Grazie!</p>
                    <button @click="lightbox.reporting = false; lightbox.reportSent = false"
                            class="mt-4 px-4 py-2 rounded-lg bg-zinc-700 hover:bg-zinc-600 text-sm text-white transition-colors">
                        Chiudi
                    </button>
                </div>

                {{-- Form --}}
                <div x-show="!lightbox.reportSent">
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider mb-2">Motivo</label>
                        <select x-model="lightbox.reportReason"
                                class="w-full bg-zinc-800 border border-zinc-700 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-indigo-500">
                            <option value="inappropriate">Contenuto inappropriato</option>
                            <option value="privacy">Violazione della privacy</option>
                            <option value="copyright">Copyright / uso non autorizzato</option>
                            <option value="other">Altro</option>
                        </select>
                    </div>

                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider mb-2">Commento <span class="normal-case font-normal text-zinc-500">(opzionale)</span></label>
                        <textarea x-model="lightbox.reportComment"
                                  rows="3"
                                  maxlength="500"
                                  placeholder="Descrivi brevemente il problema..."
                                  class="w-full bg-zinc-800 border border-zinc-700 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-indigo-500 resize-none placeholder-zinc-600"></textarea>
                    </div>

                    <p x-show="lightbox.reportError" x-text="lightbox.reportError"
                       class="text-xs text-red-400 mb-3"></p>

                    <div class="flex gap-3">
                        <button @click="lightbox.reporting = false"
                                class="flex-1 py-2.5 rounded-lg bg-zinc-700 hover:bg-zinc-600 text-sm text-zinc-300 hover:text-white transition-colors">
                            Annulla
                        </button>
                        <button @click="submitReport()"
                                :disabled="lightbox.reportSubmitting"
                                class="flex-1 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 disabled:opacity-50 text-sm font-medium text-white transition-colors">
                            <span x-show="!lightbox.reportSubmitting">Invia segnalazione</span>
                            <span x-show="lightbox.reportSubmitting">Invio...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /lightbox --}}

</div>{{-- /x-data --}}
@endsection

@section('scripts')
<script>
function gallery(photos) {
    return {
        photos,
        lightbox: {
            open:             false,
            index:            0,
            reporting:        false,
            reportSent:       false,
            reportSubmitting: false,
            reportReason:     'inappropriate',
            reportComment:    '',
            reportError:      '',
            shareSuccess:     false,
            touchStartX:      0,
            touchStartY:      0,
        },

        get current() {
            return this.photos[this.lightbox.index] ?? null;
        },

        init() {
            // Auto-open lightbox when ?foto={id} is present in the URL
            const params = new URLSearchParams(window.location.search);
            const fotoId = parseInt(params.get('foto') || '0', 10);
            if (fotoId) {
                const idx = this.photos.findIndex(p => p.id === fotoId);
                if (idx !== -1) {
                    this.$nextTick(() => this.openPhoto(idx));
                }
            }
        },

        openPhoto(i) {
            this.lightbox.index     = i;
            this.lightbox.open      = true;
            this.lightbox.reporting = false;
            this.lightbox.reportSent = false;
            document.body.style.overflow = 'hidden';
        },

        closeLightbox() {
            this.lightbox.open      = false;
            this.lightbox.reporting = false;
            document.body.style.overflow = '';
        },

        prev() {
            this.lightbox.index     = (this.lightbox.index - 1 + this.photos.length) % this.photos.length;
            this.lightbox.reporting = false;
        },

        next() {
            this.lightbox.index     = (this.lightbox.index + 1) % this.photos.length;
            this.lightbox.reporting = false;
        },

        // ── Touch / swipe ──────────────────────────────────────────────────
        touchStart(e) {
            this.lightbox.touchStartX = e.touches[0].clientX;
            this.lightbox.touchStartY = e.touches[0].clientY;
        },

        touchEnd(e) {
            const dx = e.changedTouches[0].clientX - this.lightbox.touchStartX;
            const dy = e.changedTouches[0].clientY - this.lightbox.touchStartY;
            // Only trigger swipe if horizontal movement dominates
            if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 48) {
                dx < 0 ? this.next() : this.prev();
            }
        },

        // ── Share ──────────────────────────────────────────────────────────
        async share() {
            // Build a photo-specific URL so social previews show this photo's OG image
            const url = new URL(window.location.href);
            url.searchParams.set('foto', this.current.id);
            const pageUrl = url.toString();

            if (navigator.share) {
                try {
                    await navigator.share({
                        title: document.title,
                        url:   pageUrl,
                    });
                } catch (err) {
                    // User cancelled (AbortError) — do nothing; other errors → clipboard fallback
                    if (err.name !== 'AbortError') {
                        await this.copyToClipboard(pageUrl);
                    }
                }
            } else {
                await this.copyToClipboard(pageUrl);
            }
        },

        async copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                this.lightbox.shareSuccess = true;
                setTimeout(() => { this.lightbox.shareSuccess = false; }, 2500);
            } catch (_) {
                // clipboard API not available — silent fail
            }
        },

        // ── Report ─────────────────────────────────────────────────────────
        async submitReport() {
            const photo = this.current;
            if (!photo || !photo.reportUrl) return;

            this.lightbox.reportSubmitting = true;
            this.lightbox.reportError      = '';

            try {
                const res = await fetch(photo.reportUrl, {
                    method:  'POST',
                    headers: {
                        'Content-Type':  'application/json',
                        'Accept':        'application/json',
                        'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        reason:  this.lightbox.reportReason,
                        comment: this.lightbox.reportComment,
                    }),
                });

                if (res.ok) {
                    this.lightbox.reportSent    = true;
                    this.lightbox.reportComment = '';
                    this.lightbox.reportReason  = 'inappropriate';
                } else if (res.status === 429) {
                    this.lightbox.reportError = 'Hai inviato troppe segnalazioni. Riprova tra qualche minuto.';
                } else {
                    this.lightbox.reportError = 'Si è verificato un errore. Riprova.';
                }
            } catch (_) {
                this.lightbox.reportError = 'Connessione non riuscita. Controlla la rete e riprova.';
            } finally {
                this.lightbox.reportSubmitting = false;
            }
        },
    };
}
</script>
@endsection
