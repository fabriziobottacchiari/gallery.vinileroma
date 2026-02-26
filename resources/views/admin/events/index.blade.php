@extends('layouts.admin')

@section('title', 'Eventi')

@section('header-actions')
    <a href="{{ route('admin.events.create') }}"
       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nuovo evento
    </a>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

    @if($events->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm font-medium">Nessun evento ancora.</p>
            <a href="{{ route('admin.events.create') }}" class="mt-2 inline-block text-sm text-indigo-600 hover:underline">Crea il primo evento</a>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/70">
                    <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wider w-16">Foto</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wider">Titolo</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wider hidden sm:table-cell">Data</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wider">Stato</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($events as $event)
                    @php
                        $thumb  = $event->getFirstMediaUrl('gallery', 'thumb');
                        $formId = 'delete-event-' . $event->id;
                    @endphp
                    <tr class="hover:bg-gray-50/60 transition-colors">

                        {{-- Thumbnail --}}
                        <td class="px-5 py-3">
                            @if($thumb)
                                <img src="{{ $thumb }}" alt="" class="h-10 w-10 rounded-lg object-cover border border-gray-100">
                            @else
                                <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </td>

                        {{-- Title + slug --}}
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-800">{{ $event->title }}</p>
                            <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $event->slug }}</p>
                        </td>

                        {{-- Date --}}
                        <td class="px-5 py-3 text-gray-500 whitespace-nowrap hidden sm:table-cell">
                            {{ $event->event_date->format('d/m/Y') }}
                        </td>

                        {{-- Status badges --}}
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap gap-1.5">
                                @if($event->status === 'published')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Pubblicato
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-full">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span> Bozza
                                    </span>
                                @endif

                                @if($event->is_private)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200 px-2.5 py-1 rounded-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Privato
                                    </span>
                                @endif

                                @if($event->has_watermark)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-blue-50 text-blue-500 border border-blue-100 px-2.5 py-1 rounded-full">WM</span>
                                @endif
                            </div>
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1">

                                <a href="{{ route('admin.events.photos', $event) }}"
                                   class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-200 hover:border-indigo-400 px-2.5 py-1.5 rounded-lg transition-colors font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Foto
                                </a>

                                @if($event->status === 'published')
                                    <a href="{{ route('public.events.show', $event) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-indigo-600 border border-gray-200 hover:border-indigo-300 px-2.5 py-1.5 rounded-lg transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                        Anteprima
                                    </a>
                                @endif

                                <a href="{{ route('admin.events.edit', $event) }}"
                                   class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-indigo-600 border border-gray-200 hover:border-indigo-300 px-2.5 py-1.5 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Modifica
                                </a>

                                {{-- Hidden delete form + Alpine modal trigger --}}
                                <form id="{{ $formId }}" method="POST"
                                      action="{{ route('admin.events.destroy', $event) }}" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                                <button type="button"
                                        @click="confirmDelete('{{ $formId }}', 'Eliminare l\'evento «{{ addslashes($event->title) }}»? Tutti i dati e le foto associate verranno cancellati.')"
                                        class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-red-600 border border-transparent hover:border-red-200 px-2.5 py-1.5 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Elimina
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($events->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $events->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
