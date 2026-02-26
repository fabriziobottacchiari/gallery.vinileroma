@extends('layouts.admin')

@section('title', 'Modifica evento')

@section('header-actions')
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.events.photos', $event) }}"
           class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 border border-indigo-300 hover:border-indigo-500 px-3 py-1.5 rounded-lg transition-colors font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Gestisci foto
        </a>
        @if($event->status === 'published')
        <a href="{{ route('public.events.show', $event->publicRouteParams()) }}" target="_blank"
           class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-indigo-700 border border-gray-300 hover:border-indigo-300 px-3 py-1.5 rounded-lg transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            Anteprima pubblica
        </a>
        @endif
        <a href="{{ route('admin.events.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Torna agli eventi
        </a>
    </div>
@endsection

@section('content')
<div class="max-w-2xl"
     x-data="{
        title: '{{ old('title', addslashes($event->title)) }}',
        slug: '{{ old('slug', $event->slug) }}',
        eventDate: '{{ old('event_date', $event->event_date->format('Y-m-d')) }}',
        slugLocked: true,
        generateSlug(val) {
            return val.toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        },
        get urlPreview() {
            if (!this.slug || !this.eventDate) return null;
            const d = new Date(this.eventDate);
            if (isNaN(d)) return null;
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            return '/evento/' + y + '/' + m + '/' + this.slug;
        }
     }">

    <form method="POST" action="{{ route('admin.events.update', $event) }}">
        @csrf @method('PATCH')

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">

            {{-- Title --}}
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Titolo <span class="text-red-500">*</span></label>
                <input id="title" name="title" type="text"
                       x-model="title"
                       @input="if (!slugLocked) slug = generateSlug(title)"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm
                              {{ $errors->has('title') ? 'border-red-400' : '' }}">
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Slug --}}
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">
                    Slug <span class="text-red-500">*</span>
                    <span class="ml-1 text-xs text-gray-400 font-normal">(solo minuscole, numeri e trattini)</span>
                </label>
                <div class="flex items-center gap-2">
                    <input id="slug" name="slug" type="text"
                           x-model="slug"
                           @input="slugLocked = true"
                           class="flex-1 font-mono border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm
                                  {{ $errors->has('slug') ? 'border-red-400' : '' }}">
                    <button type="button"
                            @click="slugLocked = false; slug = generateSlug(title)"
                            title="Rigenera dallo slug"
                            class="p-2 text-gray-400 hover:text-indigo-600 transition-colors rounded-lg hover:bg-indigo-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
                @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1.5 text-xs text-gray-400 font-mono">
                    URL: <span class="text-indigo-500" x-text="urlPreview"></span>
                </p>
            </div>

            {{-- Event date --}}
            <div>
                <label for="event_date" class="block text-sm font-medium text-gray-700 mb-1">Data evento <span class="text-red-500">*</span></label>
                <input id="event_date" name="event_date" type="date"
                       x-model="eventDate"
                       value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}"
                       class="border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm
                              {{ $errors->has('event_date') ? 'border-red-400' : '' }}">
                @error('event_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Note / Descrizione</label>
                <textarea id="description" name="description" rows="4"
                          class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm resize-y">{{ old('description', $event->description) }}</textarea>
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Status --}}
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Stato</label>
                <select id="status" name="status"
                        class="border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="draft"     {{ old('status', $event->status) === 'draft'     ? 'selected' : '' }}>Bozza</option>
                    <option value="published" {{ old('status', $event->status) === 'published' ? 'selected' : '' }}>Pubblicato</option>
                </select>
            </div>

            {{-- Toggles --}}
            <div class="grid grid-cols-2 gap-4 pt-1">
                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="is_private" value="1"
                           {{ old('is_private', $event->is_private) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Evento privato</p>
                        <p class="text-xs text-gray-400">Aggiunge noindex SEO</p>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="has_watermark" value="1"
                           {{ old('has_watermark', $event->has_watermark) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Applica watermark</p>
                        <p class="text-xs text-gray-400">Sovrappone il logo sulle foto</p>
                    </div>
                </label>
            </div>

        </div>

        <div class="flex items-center justify-between mt-4">
            <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
                  onsubmit="return confirm('Eliminare definitivamente «{{ addslashes($event->title) }}»?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-sm text-red-500 hover:text-red-700 flex items-center gap-1.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Elimina evento
                </button>
            </form>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.events.index') }}"
                   class="text-sm text-gray-600 hover:text-gray-800 px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                    Annulla
                </a>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Salva modifiche
                </button>
            </div>
        </div>

    </form>
</div>
@endsection
