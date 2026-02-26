@extends('layouts.admin')

@section('title', 'Nuovo evento')

@section('header-actions')
    <a href="{{ route('admin.events.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Torna agli eventi
    </a>
@endsection

@section('content')
<div class="max-w-2xl"
     x-data="{
        title: '{{ old('title', '') }}',
        slug: '{{ old('slug', '') }}',
        slugLocked: {{ old('slug') ? 'true' : 'false' }},
        generateSlug(val) {
            return val.toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        }
     }">

    <form method="POST" action="{{ route('admin.events.store') }}">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">

            {{-- Title --}}
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Titolo <span class="text-red-500">*</span></label>
                <input id="title" name="title" type="text"
                       x-model="title"
                       @input="if (!slugLocked) slug = generateSlug(title)"
                       value="{{ old('title') }}"
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
            </div>

            {{-- Event date --}}
            <div>
                <label for="event_date" class="block text-sm font-medium text-gray-700 mb-1">Data evento <span class="text-red-500">*</span></label>
                <input id="event_date" name="event_date" type="date"
                       value="{{ old('event_date') }}"
                       class="border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm
                              {{ $errors->has('event_date') ? 'border-red-400' : '' }}">
                @error('event_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Note / Descrizione</label>
                <textarea id="description" name="description" rows="4"
                          class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm resize-y">{{ old('description') }}</textarea>
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Status --}}
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Stato</label>
                <select id="status" name="status"
                        class="border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="draft"     {{ old('status', 'draft') === 'draft'     ? 'selected' : '' }}>Bozza</option>
                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Pubblicato</option>
                </select>
            </div>

            {{-- Toggles --}}
            <div class="grid grid-cols-2 gap-4 pt-1">
                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="is_private" value="1" {{ old('is_private') ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Evento privato</p>
                        <p class="text-xs text-gray-400">Aggiunge noindex SEO</p>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="has_watermark" value="1" {{ old('has_watermark', '1') ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Applica watermark</p>
                        <p class="text-xs text-gray-400">Sovrappone il logo sulle foto</p>
                    </div>
                </label>
            </div>

        </div>

        <div class="flex items-center justify-end gap-3 mt-4">
            <a href="{{ route('admin.events.index') }}"
               class="text-sm text-gray-600 hover:text-gray-800 px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                Annulla
            </a>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Crea evento
            </button>
        </div>

    </form>
</div>
@endsection
