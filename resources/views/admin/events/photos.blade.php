@extends('layouts.admin')

@section('title', 'Foto — ' . $event->title)

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

    /* Gallery drag */
    .drag-handle { cursor: grab; }
    .drag-handle:active { cursor: grabbing; }
    .sortable-ghost { opacity: 0.35; }
</style>
@endpush

@section('header-actions')
    <div class="flex items-center gap-3">
        @if($event->status === 'published')
        <a href="{{ route('public.events.show', $event->publicRouteParams()) }}" target="_blank"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            Anteprima
        </a>
        @endif

        @if($hasOriginals)
        <a href="{{ route('admin.events.photos.download-zip', $event) }}"
           class="inline-flex items-center gap-1.5 text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg font-medium transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Scarica ZIP originali
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
<div class="space-y-5">

    {{-- ── Sezione 1: Galleria ─────────────────────────────────────────── --}}
    @php
        $galleryUrls = [
            'reorder'     => route('admin.events.photos.reorder', $event),
            'bulkDestroy' => route('admin.events.photos.bulk-destroy', $event),
            'destroyBase' => url('/admin/events/' . $event->id . '/photos'),
            'csrf'        => csrf_token(),
        ];
    @endphp

    <div
        x-data="galleryManager(@js($galleryPhotos), @js($galleryUrls))"
        x-init="init()"
        class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                Galleria
                <span class="text-xs font-normal text-gray-400" x-text="photos.length + ' foto'"></span>
                <span x-show="saving" x-cloak class="text-xs text-indigo-500 animate-pulse">— Salvataggio ordine…</span>
            </h2>
            <div class="flex items-center gap-2">
                <button
                    @click="selectAll()"
                    x-show="photos.length > 0 && selected.length < photos.length"
                    class="text-xs text-gray-500 hover:text-gray-800 transition-colors px-2 py-1 rounded hover:bg-gray-100">
                    Seleziona tutte
                </button>
                <button
                    @click="clearSelection()"
                    x-show="selected.length > 0"
                    x-cloak
                    class="text-xs text-gray-500 hover:text-gray-800 transition-colors px-2 py-1 rounded hover:bg-gray-100">
                    Deseleziona
                </button>
            </div>
        </div>

        {{-- Bulk action bar --}}
        <div
            x-show="selected.length > 0"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="flex items-center gap-3 px-5 py-3 bg-red-50 border-b border-red-100">
            <span class="text-sm font-medium text-red-700">
                <span x-text="selected.length"></span> foto selezionate
            </span>
            <button
                @click="bulkDestroy()"
                class="inline-flex items-center gap-1.5 text-xs font-medium bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Elimina selezionate
            </button>
            <button
                @click="clearSelection()"
                class="text-xs text-red-600 hover:text-red-800 px-2 py-1 rounded transition-colors">
                Annulla
            </button>
        </div>

        {{-- Empty state --}}
        <div x-show="photos.length === 0" class="flex flex-col items-center justify-center py-16 text-center px-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm text-gray-400">Nessuna foto ancora elaborata.</p>
            <p class="text-xs text-gray-300 mt-1">Carica le foto nella sezione qui sotto.</p>
        </div>

        {{-- SortableJS grid --}}
        <div x-show="photos.length > 0" class="p-4">
            <div
                x-ref="sortableGrid"
                class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2">

                <template x-for="photo in photos" :key="photo.mediaId">
                    <div
                        :data-media-id="photo.mediaId"
                        class="relative aspect-square rounded-lg overflow-hidden bg-gray-100 group select-none">

                        {{-- Thumbnail --}}
                        <img :src="photo.thumb" class="w-full h-full object-cover pointer-events-none" loading="lazy">

                        {{-- Hover/selected overlay --}}
                        <div
                            class="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 transition-opacity duration-150"
                            :class="{ 'opacity-100': isSelected(photo.mediaId) }">
                        </div>

                        {{-- Hidden photo full overlay --}}
                        <div
                            x-show="photo.isHidden"
                            class="absolute inset-0 bg-black/50 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </div>

                        {{-- Checkbox — top left --}}
                        <button
                            @click.stop="toggleSelect(photo.mediaId)"
                            class="absolute top-1.5 left-1.5 h-5 w-5 rounded border-2 flex items-center justify-center transition-all duration-100 opacity-0 group-hover:opacity-100 z-10"
                            :class="{
                                'opacity-100 bg-indigo-500 border-indigo-500 text-white': isSelected(photo.mediaId),
                                'bg-white/90 border-gray-300': !isSelected(photo.mediaId)
                            }">
                            <svg x-show="isSelected(photo.mediaId)" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        {{-- Delete button — top right --}}
                        <button
                            @click.stop="destroySingle(photo)"
                            x-show="photo.uploadId"
                            class="absolute top-1.5 right-1.5 h-6 w-6 rounded-full bg-black/50 hover:bg-red-500 text-white flex items-center justify-center transition-all duration-100 opacity-0 group-hover:opacity-100 z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        {{-- Cover star — bottom left --}}
                        <button
                            @click.stop="setCover(photo)"
                            x-show="photo.coverUrl"
                            class="absolute bottom-1.5 left-1.5 h-6 w-6 rounded-full flex items-center justify-center transition-all duration-100 z-10"
                            :class="{
                                'opacity-100 bg-yellow-400 text-yellow-900 shadow-sm': photo.isCover,
                                'opacity-0 group-hover:opacity-100 bg-black/50 hover:bg-yellow-400 text-white hover:text-yellow-900': !photo.isCover
                            }"
                            :title="photo.isCover ? 'Copertina attuale' : 'Imposta come copertina'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </button>

                        {{-- Drag handle — bottom right --}}
                        <div class="drag-handle absolute bottom-1.5 right-1.5 h-6 w-6 rounded flex items-center justify-center bg-black/40 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-100 z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
                                <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                                <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
                            </svg>
                        </div>

                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ── Sezione 2: Caricamento ───────────────────────────────────────── --}}
    <div
        x-data="photoManager()"
        x-init="init()"
        class="space-y-5">

        {{-- Stats bar --}}
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

        {{-- Queue worker notice --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 flex items-start gap-3 text-sm text-blue-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                Per elaborare le foto devi avere il worker attivo:
                <code class="ml-1 bg-blue-100 px-1.5 py-0.5 rounded font-mono text-xs">php artisan queue:work --queue=default</code>
            </div>
        </div>

        {{-- FilePond dropzone --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-800 text-sm">Carica foto</h2>
                <span class="text-xs text-gray-400">JPG, PNG, WebP · max 20 MB per file</span>
            </div>
            <input type="file" id="photo-input" multiple accept="image/*">
        </div>

        {{-- Toast notification --}}
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

        {{-- Status list --}}
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
                                    <span x-show="item.status === 'completed'"
                                          class="inline-flex items-center gap-1.5 text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Completata
                                    </span>
                                    <span x-show="item.status === 'processing'"
                                          class="inline-flex items-center gap-1.5 text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-full">
                                        <svg class="h-3 w-3 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                        </svg>
                                        Elaborazione
                                    </span>
                                    <span x-show="item.status === 'pending'"
                                          class="inline-flex items-center gap-1.5 text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200 px-2.5 py-1 rounded-full">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                        In coda
                                    </span>
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

    </div>{{-- /photoManager --}}

</div>{{-- /outer wrapper --}}
@endsection

@section('scripts')
{{-- SortableJS --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
{{-- FilePond --}}
<script src="https://unpkg.com/filepond@4.30.6/dist/filepond.min.js"></script>

<script>
function galleryManager(photosData, urls) {
    return {
        photos:   photosData,
        selected: [],
        saving:   false,
        sortable: null,

        reorderUrl:    urls.reorder,
        bulkDestroyUrl: urls.bulkDestroy,
        destroyBase:   urls.destroyBase,
        csrfToken:     urls.csrf,

        init() {
            this.$nextTick(() => this.initSortable());
        },

        initSortable() {
            const el = this.$refs.sortableGrid;
            if (! el || typeof Sortable === 'undefined') return;

            this.sortable = Sortable.create(el, {
                animation: 150,
                handle:    '.drag-handle',
                ghostClass: 'sortable-ghost',
                onEnd: (evt) => {
                    if (evt.oldIndex === evt.newIndex) return;
                    // Sync photos array with new DOM order
                    const moved = this.photos.splice(evt.oldIndex, 1)[0];
                    this.photos.splice(evt.newIndex, 0, moved);
                    this.$nextTick(() => this.saveOrder());
                },
            });
        },

        saveOrder() {
            const mediaIds = this.photos.map(p => p.mediaId);
            this.saving = true;

            fetch(this.reorderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept':       'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ media_ids: mediaIds }),
            })
            .then(() => { this.saving = false; })
            .catch(() => { this.saving = false; });
        },

        toggleSelect(mediaId) {
            const idx = this.selected.indexOf(mediaId);
            idx === -1 ? this.selected.push(mediaId) : this.selected.splice(idx, 1);
        },

        isSelected(mediaId) {
            return this.selected.includes(mediaId);
        },

        selectAll() {
            this.selected = this.photos.map(p => p.mediaId);
        },

        clearSelection() {
            this.selected = [];
        },

        async setCover(photo) {
            if (! photo.coverUrl) return;

            await fetch(photo.coverUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept':       'application/json',
                },
                credentials: 'same-origin',
            });

            this.photos.forEach(p => { p.isCover = (p.mediaId === photo.mediaId); });
        },

        async bulkDestroy() {
            if (this.selected.length === 0) return;
            if (! confirm(`Eliminare ${this.selected.length} foto? L'operazione non è reversibile.`)) return;

            const ids = [...this.selected];

            const res = await fetch(this.bulkDestroyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept':       'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ media_ids: ids }),
            });

            if (res.ok) {
                this.photos   = this.photos.filter(p => ! ids.includes(p.mediaId));
                this.selected = [];
            }
        },

        async destroySingle(photo) {
            if (! photo.uploadId) return;
            if (! confirm('Eliminare questa foto?')) return;

            const url = `${this.destroyBase}/${photo.uploadId}`;

            const res = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept':       'application/json',
                },
                credentials: 'same-origin',
            });

            if (res.ok) {
                this.photos   = this.photos.filter(p => p.mediaId !== photo.mediaId);
                this.selected = this.selected.filter(id => id !== photo.mediaId);
            }
        },
    };
}

function photoManager() {
    return {
        stats:        { total: 0, pending: 0, processing: 0, completed: 0, failed: 0, items: [] },
        notification: null,
        pollTimer:    null,
        wasActive:    false,
        batchUuid:    crypto.randomUUID(),

        eventId:   {{ $event->id }},
        csrfToken: '{{ csrf_token() }}',
        uploadUrl: '{{ route('admin.events.photos.upload', $event) }}',
        statusUrl: '{{ route('admin.events.photos.status', $event) }}',

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
                allowMultiple:      true,
                maxParallelUploads: 3,
                maxFileSize:        '20MB',

                server: {
                    process: {
                        url:    self.uploadUrl,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': self.csrfToken,
                            'X-Batch-Uuid': self.batchUuid,
                        },
                        withCredentials: true,
                        onload: () => {
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

                const isActive  = data.pending > 0 || data.processing > 0;
                const justEnded = this.wasActive && ! isActive && data.total > 0;

                this.stats    = data;
                this.wasActive = isActive;

                if (justEnded) {
                    this.notification = data.failed > 0
                        ? { type: 'warning', message: `${data.completed} foto completate, ${data.failed} con errori.` }
                        : { type: 'success', message: `${data.completed} foto elaborate con successo!` };

                    setTimeout(() => { this.notification = null; }, 8000);
                }

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
