@extends('layouts.public')

@section('title', 'Registrati — Vinile Roma Gallery')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-xl">

        {{-- Brand --}}
        <div class="text-center mb-8">
            <a href="{{ route('public.events.index') }}" class="inline-block text-2xl font-bold tracking-tight text-white">
                Vinile Roma Gallery
            </a>
            <p class="mt-2 text-zinc-400 text-sm">Crea il tuo account per accedere alle gallerie</p>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-8 shadow-2xl">
            <form method="POST" action="{{ route('gallery.register') }}" x-data="{ showInstagramHelp: false }">
                @csrf

                <div class="space-y-5">

                    {{-- Nome + Cognome --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-zinc-300 mb-1.5">
                                Nome <span class="text-red-400">*</span>
                            </label>
                            <input id="first_name" name="first_name" type="text" autocomplete="given-name"
                                   value="{{ old('first_name') }}"
                                   class="w-full bg-zinc-800 border {{ $errors->has('first_name') ? 'border-red-500' : 'border-zinc-700' }} rounded-lg px-4 py-2.5 text-white text-sm placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            @error('first_name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-zinc-300 mb-1.5">
                                Cognome <span class="text-red-400">*</span>
                            </label>
                            <input id="last_name" name="last_name" type="text" autocomplete="family-name"
                                   value="{{ old('last_name') }}"
                                   class="w-full bg-zinc-800 border {{ $errors->has('last_name') ? 'border-red-500' : 'border-zinc-700' }} rounded-lg px-4 py-2.5 text-white text-sm placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            @error('last_name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-zinc-300 mb-1.5">
                            Indirizzo email <span class="text-red-400">*</span>
                        </label>
                        <input id="email" name="email" type="email" autocomplete="email"
                               value="{{ old('email') }}"
                               class="w-full bg-zinc-800 border {{ $errors->has('email') ? 'border-red-500' : 'border-zinc-700' }} rounded-lg px-4 py-2.5 text-white text-sm placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Telefono --}}
                    <div>
                        <label for="phone" class="block text-sm font-medium text-zinc-300 mb-1.5">
                            Telefono <span class="text-red-400">*</span>
                        </label>
                        <input id="phone" name="phone" type="tel" autocomplete="tel"
                               value="{{ old('phone') }}"
                               placeholder="+39 333 1234567"
                               class="w-full bg-zinc-800 border {{ $errors->has('phone') ? 'border-red-500' : 'border-zinc-700' }} rounded-lg px-4 py-2.5 text-white text-sm placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('phone') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Anno di nascita + Sesso --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="birth_year" class="block text-sm font-medium text-zinc-300 mb-1.5">
                                Anno di nascita <span class="text-red-400">*</span>
                            </label>
                            <input id="birth_year" name="birth_year" type="number"
                                   value="{{ old('birth_year') }}"
                                   min="1920" max="{{ now()->year - 13 }}"
                                   placeholder="es. 1990"
                                   class="w-full bg-zinc-800 border {{ $errors->has('birth_year') ? 'border-red-500' : 'border-zinc-700' }} rounded-lg px-4 py-2.5 text-white text-sm placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            @error('birth_year') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="gender" class="block text-sm font-medium text-zinc-300 mb-1.5">
                                Sesso <span class="text-red-400">*</span>
                            </label>
                            <select id="gender" name="gender"
                                    class="w-full bg-zinc-800 border {{ $errors->has('gender') ? 'border-red-500' : 'border-zinc-700' }} rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Seleziona</option>
                                <option value="male"              {{ old('gender') === 'male'              ? 'selected' : '' }}>Uomo</option>
                                <option value="female"            {{ old('gender') === 'female'            ? 'selected' : '' }}>Donna</option>
                                <option value="prefer_not_to_say" {{ old('gender') === 'prefer_not_to_say' ? 'selected' : '' }}>Preferisco non specificare</option>
                            </select>
                            @error('gender') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Profilo Instagram (facoltativo) --}}
                    <div>
                        <label for="instagram_handle" class="block text-sm font-medium text-zinc-300 mb-1.5">
                            Profilo Instagram
                            <span class="ml-1 text-xs font-normal text-zinc-500">(facoltativo)</span>
                            @if(env('INSTAGRAM_GUIDE_URL'))
                                <a href="{{ env('INSTAGRAM_GUIDE_URL') }}" target="_blank" rel="noopener"
                                   class="ml-2 text-xs text-indigo-400 hover:text-indigo-300 underline">
                                    Come trovarlo?
                                </a>
                            @endif
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 text-sm select-none">@</span>
                            <input id="instagram_handle" name="instagram_handle" type="text"
                                   value="{{ old('instagram_handle') }}"
                                   placeholder="tuonome"
                                   class="w-full bg-zinc-800 border {{ $errors->has('instagram_handle') ? 'border-red-500' : 'border-zinc-700' }} rounded-lg pl-8 pr-4 py-2.5 text-white text-sm placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        @error('instagram_handle') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-zinc-300 mb-1.5">
                            Password <span class="text-red-400">*</span>
                        </label>
                        <input id="password" name="password" type="password" autocomplete="new-password"
                               class="w-full bg-zinc-800 border {{ $errors->has('password') ? 'border-red-500' : 'border-zinc-700' }} rounded-lg px-4 py-2.5 text-white text-sm placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <p class="mt-1 text-xs text-zinc-500">Minimo 8 caratteri</p>
                        @error('password') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-zinc-300 mb-1.5">
                            Conferma password <span class="text-red-400">*</span>
                        </label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-4 py-2.5 text-white text-sm placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    {{-- Privacy / Consensi --}}
                    <div class="pt-2 space-y-4 border-t border-zinc-800">
                        <p class="text-xs text-zinc-500 uppercase tracking-wider font-semibold">Privacy e consensi</p>

                        {{-- Privacy obbligatoria (GDPR) --}}
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input type="checkbox" name="privacy_accepted" value="1"
                                   {{ old('privacy_accepted') ? 'checked' : '' }}
                                   class="mt-0.5 h-4 w-4 shrink-0 rounded border-zinc-600 bg-zinc-800 text-indigo-500 focus:ring-indigo-500">
                            <span class="text-sm text-zinc-300 leading-relaxed">
                                Ho letto e accetto la
                                @if(env('PRIVACY_POLICY_URL'))
                                    <a href="{{ env('PRIVACY_POLICY_URL') }}" target="_blank" rel="noopener"
                                       class="text-indigo-400 hover:text-indigo-300 underline">Privacy Policy</a>
                                @else
                                    <span class="text-indigo-400">Privacy Policy</span>
                                @endif
                                ai sensi del Regolamento UE 2016/679 (GDPR). Il trattamento dei dati è necessario per la creazione e gestione del tuo account.
                                <span class="text-red-400">*</span>
                            </span>
                        </label>
                        @error('privacy_accepted') <p class="text-xs text-red-400">{{ $message }}</p> @enderror

                        {{-- Newsletter (facoltativo) --}}
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input type="checkbox" name="newsletter_consent" value="1"
                                   {{ old('newsletter_consent') ? 'checked' : '' }}
                                   class="mt-0.5 h-4 w-4 shrink-0 rounded border-zinc-600 bg-zinc-800 text-indigo-500 focus:ring-indigo-500">
                            <span class="text-sm text-zinc-400 leading-relaxed">
                                <span class="text-zinc-300 font-medium">Newsletter</span> —
                                Acconsento all'invio di comunicazioni via email relative agli eventi, gallerie e novità di Vinile Roma ai sensi dell'art. 7 GDPR.
                                <span class="text-zinc-500 text-xs">(facoltativo)</span>
                            </span>
                        </label>

                        {{-- Marketing (facoltativo) --}}
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input type="checkbox" name="marketing_consent" value="1"
                                   {{ old('marketing_consent') ? 'checked' : '' }}
                                   class="mt-0.5 h-4 w-4 shrink-0 rounded border-zinc-600 bg-zinc-800 text-indigo-500 focus:ring-indigo-500">
                            <span class="text-sm text-zinc-400 leading-relaxed">
                                <span class="text-zinc-300 font-medium">Marketing</span> —
                                Acconsento all'utilizzo dei miei dati per finalità di marketing diretto, incluso l'invio di comunicazioni promozionali personalizzate ai sensi dell'art. 7 GDPR.
                                <span class="text-zinc-500 text-xs">(facoltativo)</span>
                            </span>
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-zinc-900">
                        Crea account
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center mt-6 text-sm text-zinc-500">
            Hai già un account?
            <a href="{{ route('gallery.login') }}" class="text-indigo-400 hover:text-indigo-300 font-medium">Accedi</a>
        </p>
    </div>
</div>
@endsection
