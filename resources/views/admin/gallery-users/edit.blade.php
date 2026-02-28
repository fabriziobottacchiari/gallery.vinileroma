@extends('layouts.admin')

@section('title', 'Modifica iscritto — ' . $user->full_name)

@section('content')
<div class="max-w-2xl space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.gallery-users.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-base font-semibold text-gray-900">Modifica iscritto</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ $user->full_name }} — {{ $user->email }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.gallery-users.update', $user) }}" class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
        @csrf
        @method('PATCH')

        {{-- Personal info --}}
        <div class="px-5 py-4 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Dati personali</p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nome <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('first_name') border-red-400 @enderror">
                    @error('first_name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Cognome <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('last_name') border-red-400 @enderror">
                    @error('last_name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('email') border-red-400 @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                @if(!$user->email_verified_at)
                    <p class="mt-1 text-xs text-amber-600">Email non verificata</p>
                @endif
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Telefono <span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('phone') border-red-400 @enderror">
                @error('phone')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Anno di nascita <span class="text-red-500">*</span></label>
                    <input type="number" name="birth_year" value="{{ old('birth_year', $user->birth_year) }}" required
                           min="1900" max="{{ now()->year }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('birth_year') border-red-400 @enderror">
                    @error('birth_year')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Sesso <span class="text-red-500">*</span></label>
                    <select name="gender" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('gender') border-red-400 @enderror">
                        <option value="male"              {{ old('gender', $user->gender) === 'male'              ? 'selected' : '' }}>Uomo</option>
                        <option value="female"            {{ old('gender', $user->gender) === 'female'            ? 'selected' : '' }}>Donna</option>
                        <option value="prefer_not_to_say" {{ old('gender', $user->gender) === 'prefer_not_to_say' ? 'selected' : '' }}>Preferisco non specificare</option>
                    </select>
                    @error('gender')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Instagram</label>
                <div class="flex items-center rounded-lg border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-transparent @error('instagram_handle') border-red-400 @enderror">
                    <span class="px-3 py-2 text-sm text-gray-400 bg-gray-50 border-r border-gray-300 select-none">@</span>
                    <input type="text" name="instagram_handle" value="{{ old('instagram_handle', $user->instagram_handle) }}"
                           placeholder="username"
                           class="flex-1 px-3 py-2 text-sm focus:outline-none">
                </div>
                @error('instagram_handle')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- GDPR (read-only) --}}
        <div class="px-5 py-4 space-y-2">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Consensi GDPR <span class="font-normal normal-case text-gray-400">(sola lettura)</span></p>
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <span class="{{ $user->privacy_accepted ? 'text-emerald-500' : 'text-gray-300' }}">{{ $user->privacy_accepted ? '✓' : '✗' }}</span>
                Privacy accettata
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <span class="{{ $user->newsletter_consent ? 'text-emerald-500' : 'text-gray-300' }}">{{ $user->newsletter_consent ? '✓' : '✗' }}</span>
                Consenso newsletter
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <span class="{{ $user->marketing_consent ? 'text-emerald-500' : 'text-gray-300' }}">{{ $user->marketing_consent ? '✓' : '✗' }}</span>
                Consenso marketing
            </div>
        </div>

        {{-- Actions --}}
        <div class="px-5 py-4 flex items-center justify-between">
            <a href="{{ route('admin.gallery-users.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                Annulla
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Salva modifiche
            </button>
        </div>
    </form>

</div>
@endsection
