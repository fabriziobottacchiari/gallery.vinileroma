@extends('layouts.admin')

@section('title', 'Iscritti')

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-900">Utenti registrati alla galleria</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ $users->total() }} {{ $tab === 'deleted' ? 'eliminati' : 'iscritti totali' }}</p>
        </div>

        @if($tab !== 'deleted')
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                    class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium px-3.5 py-2 rounded-lg transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Esporta CSV
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open"
                 x-cloak
                 @click.outside="open = false"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-1 w-52 bg-white border border-gray-200 rounded-xl shadow-lg z-10 py-1 origin-top-right">

                <a href="{{ route('admin.gallery-users.export-csv', ['filter' => 'all']) }}"
                   class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                    <span class="h-2 w-2 rounded-full bg-gray-400 shrink-0"></span>
                    Tutti gli iscritti
                </a>
                <a href="{{ route('admin.gallery-users.export-csv', ['filter' => 'newsletter']) }}"
                   class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                    <span class="h-2 w-2 rounded-full bg-indigo-500 shrink-0"></span>
                    Con consenso newsletter
                </a>
                <a href="{{ route('admin.gallery-users.export-csv', ['filter' => 'marketing']) }}"
                   class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 shrink-0"></span>
                    Con consenso marketing
                </a>
            </div>
        </div>
        @endif
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div class="flex gap-1 border-b border-gray-200">
        <a href="{{ route('admin.gallery-users.index') }}"
           class="px-4 py-2 text-sm font-medium transition-colors border-b-2 -mb-px
                  {{ $tab !== 'deleted' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Attivi
        </a>
        <a href="{{ route('admin.gallery-users.index', ['tab' => 'deleted']) }}"
           class="px-4 py-2 text-sm font-medium transition-colors border-b-2 -mb-px
                  {{ $tab === 'deleted' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Eliminati
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3">Nome</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Telefono</th>
                        <th class="px-4 py-3">Nascita</th>
                        <th class="px-4 py-3">Sesso</th>
                        <th class="px-4 py-3">Instagram</th>
                        <th class="px-4 py-3 text-center">Verificato</th>
                        <th class="px-4 py-3 text-center">Newsletter</th>
                        <th class="px-4 py-3 text-center">Marketing</th>
                        <th class="px-4 py-3">
                            {{ $tab === 'deleted' ? 'Eliminato il' : 'Iscritto il' }}
                        </th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors {{ $tab === 'deleted' ? 'opacity-60' : '' }}">
                        <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                            {{ $user->full_name }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $user->phone }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->birth_year }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ \App\Models\GalleryUser::genderLabel($user->gender) }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            @if($user->instagram_handle)
                                <a href="https://instagram.com/{{ $user->instagram_handle }}" target="_blank" rel="noopener"
                                   class="text-indigo-600 hover:text-indigo-800">@{{ $user->instagram_handle }}</a>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($user->email_verified_at)
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 text-amber-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($user->newsletter_consent)
                                <span class="text-emerald-500">✓</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($user->marketing_consent)
                                <span class="text-emerald-500">✓</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap text-xs">
                            @if($tab === 'deleted')
                                {{ $user->deleted_at->format('d/m/Y H:i') }}
                            @else
                                {{ $user->created_at->format('d/m/Y H:i') }}
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            @if($tab === 'deleted')
                                {{-- Restore --}}
                                <form method="POST" action="{{ route('admin.gallery-users.restore', $user->id) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                                        Ripristina
                                    </button>
                                </form>
                            @else
                                <div class="flex items-center justify-end gap-3">
                                    {{-- Edit --}}
                                    <a href="{{ route('admin.gallery-users.edit', $user) }}"
                                       class="text-xs text-gray-500 hover:text-gray-800 font-medium transition-colors">
                                        Modifica
                                    </a>
                                    {{-- Delete --}}
                                    <form method="POST" action="{{ route('admin.gallery-users.destroy', $user) }}"
                                          x-data
                                          @submit.prevent="if(confirm('Eliminare {{ addslashes($user->full_name) }}? L\'utente potrà essere ripristinato in seguito.')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors">
                                            Elimina
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-4 py-12 text-center text-gray-400 text-sm">
                            {{ $tab === 'deleted' ? 'Nessun utente eliminato.' : 'Nessun utente registrato.' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $users->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
