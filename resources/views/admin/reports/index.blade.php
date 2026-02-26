@extends('layouts.admin')

@section('title', 'Segnalazioni')

@section('content')

@php
$reasons = [
    'inappropriate' => ['label' => 'Contenuto inappropriato', 'class' => 'bg-red-50 text-red-700 border-red-200'],
    'privacy'       => ['label' => 'Violazione privacy',      'class' => 'bg-purple-50 text-purple-700 border-purple-200'],
    'copyright'     => ['label' => 'Copyright',               'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
    'other'         => ['label' => 'Altro',                   'class' => 'bg-gray-100 text-gray-600 border-gray-200'],
];
@endphp

<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

    @if($reports->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
            </svg>
            <p class="text-sm font-medium">Nessuna segnalazione ricevuta.</p>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/70">
                    <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wider w-16">Foto</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wider">Evento</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wider">Motivo</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wider hidden md:table-cell">Commento</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wider hidden lg:table-cell">Data</th>
                    <th class="px-5 py-3 w-16"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($reports as $report)
                    @php
                        $upload   = $report->photoUpload;
                        $media    = ($upload && $upload->media_id)
                                    ? $report->event->getMedia('gallery')->firstWhere('id', $upload->media_id)
                                    : null;
                        $thumb    = $media?->getUrl('thumb');
                        $formId   = 'delete-report-' . $report->id;
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

                        {{-- Event --}}
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-800">{{ $report->event->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $report->event->event_date->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-400 font-mono mt-0.5">IP: {{ substr($report->reporter_ip, 0, strrpos($report->reporter_ip, '.') ?: strlen($report->reporter_ip)) }}.***</p>
                        </td>

                        {{-- Reason --}}
                        <td class="px-5 py-3">
                            @php $r = $reasons[$report->reason] ?? ['label' => $report->reason, 'class' => 'bg-gray-100 text-gray-600 border-gray-200']; @endphp
                            <span class="inline-flex text-xs font-medium border px-2.5 py-1 rounded-full {{ $r['class'] }}">
                                {{ $r['label'] }}
                            </span>
                        </td>

                        {{-- Comment --}}
                        <td class="px-5 py-3 hidden md:table-cell text-gray-500 max-w-xs">
                            @if($report->comment)
                                <span title="{{ $report->comment }}">
                                    {{ Str::limit($report->comment, 60) }}
                                </span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        {{-- Date --}}
                        <td class="px-5 py-3 hidden lg:table-cell text-gray-400 whitespace-nowrap text-xs">
                            {{ $report->created_at->format('d/m/Y H:i') }}
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-3 text-right">
                            <form id="{{ $formId }}"
                                  method="POST"
                                  action="{{ route('admin.reports.destroy', $report) }}">
                                @csrf @method('DELETE')
                            </form>
                            <button type="button"
                                    @click="confirmDelete('{{ $formId }}', 'Eliminare questa segnalazione?')"
                                    class="inline-flex items-center text-xs text-gray-400 hover:text-red-600 border border-transparent hover:border-red-200 px-2.5 py-1.5 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($reports->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $reports->links() }}
            </div>
        @endif
    @endif

</div>
@endsection
