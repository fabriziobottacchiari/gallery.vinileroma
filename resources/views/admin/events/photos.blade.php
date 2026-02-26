@extends('layouts.admin')

@section('title', 'Foto — ' . $event->title)

{{-- FilePond CSS --}}
@push('head')
<link href="https://unpkg.com/filepond@4.30.6/dist/filepond.min.css" rel="stylesheet">
<style>
    [x-cloak] { display: none !important; }

    /* FilePond theme overrides */
    .filepond--root { font-family: inherit; }
    .filepond--panel-root { background-color: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 12px; }
    .filepond--root:hover .filepond--panel-root { border-color: #818cf8; background-color: #eef2ff; }
    .filepond--drop-label { color: #64748b; }
    .filepond--drop-label label { cursor: pointer; }
    .filepond--label-action { color: #6366f1; text-decoration-color: #6366f1; }
</style>
@endpush

@section('header-actions')
    <div class="flex items-center gap-3">
        @if($event->status === 'published')
        <a href="{{ route('events.show', $event) }}" target="_blank"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            Anteprima
        </a>
        @endif
        <a href="{{ route('admin.events.edit', $event) }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Torna all'evento
        </a>
    </div>
@endsection

@section('content')
<div
    x-data="photoManager()"
    x-init="init()"
    class="space-y-5">

    {{-- ── Stats bar ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-4 gap-4" x-show="stats.total > 0">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900" x-text="stats.total"></p>
            <p class="text-xs text-gray-500 mt-0.5">Totali</p>
        </div>
        <div class="bg-emerald-50 rounded-xl border border-emerald-200 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-700" x-text="stats.completed"></p>
            <p class="text-xs text-emerald-600 mt-0.5">Completate</p>
        </div>
        <div class="bg-amber-50 rounded-xl border border-amber-200 p-4 text-center">
            <p class="text-2xl font-bold text-amber-700" x-text="stats.pending + stats.processing"></p>
            <p class="text-xs text-amber-600 mt-0.5">In elaborazione</p>
        </div>
        <div class="bg-red-50 rounded-xl border border-red-200 p-4 text-center">
            <p class="text-2xl font-bold text-red-700" x-text="stats.failed"></p>
            <p class="text-xs text-red-600 mt-0.5">Errori</p>
        </div>
    </div>

    {{-- ── Queue worker notice ─────────────────────────────────────────── --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 flex items-start gap-3 text-sm text-blue-800">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            Per elaborare le foto devi avere il worker attivo:
            <code class="ml-1 bg-blue-100 px-1.5 py-0.5 rounded font-mono text-xs">php artisan queue:work --queue=default</code>
        </div>
    </div>

    {{-- ── FilePond dropzone ────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800 text-sm">Carica foto</h2>
            <span class="text-xs text-gray-400">JPG, PNG, WebP · max 20 MB per file</span>
        </div>
        <input type="file" id="photo-input" multiple accept="image/*">
    </div>

    {{-- ── Toast notification ───────────────────────────────────────────── --}}
    <div x-show="notification"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-4 rounded-xl shadow-2xl border text-sm font-medium"
         :class="notification?.type === 'success'
             ? 'bg-emerald-600 text-white border-emerald-500'
             : 'bg-amber-500 text-white border-amber-400'">
        <svg x-show="notification?.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <svg x-show="notification?.type === 'warning'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z" clip-rule="evenodd"/>
        </svg>
        <span x-text="notification?.message"></span>
        <button @click="notification = null" class="ml-2 opacity-70 hover:opacity-100 transition-opacity">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ── Status list ──────────────────────────────────────────────────── --}}
    <div x-show="stats.items.length > 0" x-cloak>
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold text-gray-800 text-sm">
                Archivio upload
                <span class="ml-1.5 text-xs font-normal text-gray-400">(ultimi 500)</span>
            </h2>
            <div class="flex items-center gap-2 text-xs text-gray-400" x-show="stats.pending + stats.processing > 0">
                <span class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
                Elaborazione in corso...
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-5 py-3 font-medium text-gray-500">File</th>
                        <th class="text-left px-5 py-3 font-medium text-gray-500">Stato</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="item in stats.items" :key="item.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 font-mono text-xs text-gray-600 truncate max-w-xs"
                                x-text="item.original_filename"></td>
                            <td class="px-5 py-3">
                                {{-- Completed --}}
                                <span x-show="item.status === 'completed'"
                                      class="inline-flex items-center gap-1.5 text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Completata
                                </span>
                                {{-- Processing --}}
                                <span x-show="item.status === 'processing'"
                                      class="inline-flex items-center gap-1.5 text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-full">
                                    <svg class="h-3 w-3 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                    </svg>
                                    Elaborazione
                                </span>
                                {{-- Pending --}}
                                <span x-show="item.status === 'pending'"
                                      class="inline-flex items-center gap-1.5 text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200 px-2.5 py-1 rounded-full">
                                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                    In coda
                                </span>
                                {{-- Failed --}}
                                <span x-show="item.status === 'failed'"
                                      class="inline-flex items-center gap-1.5 text-xs font-medium bg-red-50 text-red-700 border border-red-200 px-2.5 py-1 rounded-full"
                                      :title="item.error_message">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                    Errore
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <span x-show="item.status === 'failed'"
                                      class="text-xs text-red-500 truncate max-w-[200px] block text-right"
                                      x-text="item.error_message"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
{{-- FilePond --}}
<script src="https://unpkg.com/filepond@4.30.6/dist/filepond.min.js"></script>

<script>
function photoManager() {
    return {
        stats:        { total: 0, pending: 0, processing: 0, completed: 0, failed: 0, items: [] },
        notification: null,
        pollTimer:    null,
        wasActive:    false,
        batchUuid:    crypto.randomUUID(),

        eventId:      {{ $event->id }},
        csrfToken:    '{{ csrf_token() }}',
        uploadUrl:    '{{ route('admin.events.photos.upload', $event) }}',
        statusUrl:    '{{ route('admin.events.photos.status', $event) }}',

        init() {
            this.initFilePond();
            this.schedulePoll();
        },

        initFilePond() {
            const self = this;

            FilePond.setOptions({
                labelIdle:              'Trascina le foto qui oppure <span class="filepond--label-action">sfoglia</span>',
                labelFileProcessing:    'Caricamento…',
                labelFileProcessingComplete: 'Caricato',
                labelFileProcessingAborted:  'Annullato',
                labelFileProcessingError:    'Errore durante il caricamento',
                labelTapToCancel:       'tocca per annullare',
                labelTapToRetry:        'tocca per riprovare',
                labelTapToUndo:         'tocca per rimuovere',
                labelButtonRemoveItem:  'Rimuovi',
                labelButtonAbortItemLoad:  'Annulla',
                labelButtonRetryItemLoad:  'Riprova',
                labelButtonAbortItemProcessing: 'Annulla',
                labelButtonUndoItemProcessing:  'Annulla invio',
                labelButtonRetryItemProcessing: 'Riprova',
                labelButtonProcessItem: 'Carica',
                labelMaxFileSizeExceeded: 'File troppo grande',
                labelMaxFileSize:        'Max {filesize}',
            });

            FilePond.create(document.querySelector('#photo-input'), {
                allowMultiple: true,
                maxParallelUploads: 3,
                maxFileSize: '20MB',

                server: {
                    process: {
                        url: self.uploadUrl,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': self.csrfToken,
                            'X-Batch-Uuid': self.batchUuid,
                        },
                        withCredentials: true,
                        onload: () => {
                            // Restart polling after a file was successfully uploaded
                            clearTimeout(self.pollTimer);
                            self.schedulePoll(500);
                        },
                    },
                    revert: {
                        url:    self.uploadUrl,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': self.csrfToken,
                        },
                    },
                },
            });
        },

        schedulePoll(delay = 0) {
            clearTimeout(this.pollTimer);
            this.pollTimer = setTimeout(() => this.fetchStats(), delay);
        },

        async fetchStats() {
            try {
                const url = `${this.statusUrl}?batch=${encodeURIComponent(this.batchUuid)}`;
                const res = await fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept':       'application/json',
                    },
                    credentials: 'same-origin',
                });

                if (! res.ok) { throw new Error(`HTTP ${res.status}`); }

                const data = await res.json();

                const isActive   = data.pending > 0 || data.processing > 0;
                const justEnded  = this.wasActive && ! isActive && data.total > 0;

                this.stats    = data;
                this.wasActive = isActive;

                if (justEnded) {
                    this.notification = data.failed > 0
                        ? { type: 'warning', message: `${data.completed} foto completate, ${data.failed} con errori.` }
                        : { type: 'success', message: `${data.completed} foto elaborate con successo! 🎉` };

                    // Auto-dismiss after 8 s
                    setTimeout(() => { this.notification = null; }, 8000);
                }

                // Poll faster when active, slower when idle
                this.schedulePoll(isActive ? 2000 : 8000);

            } catch (e) {
                console.error('[PhotoManager] poll error:', e);
                this.schedulePoll(10000);
            }
        },
    };
}
</script>
@endsection
