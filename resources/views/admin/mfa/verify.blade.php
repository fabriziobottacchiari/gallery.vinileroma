<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifica accesso — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-900 text-gray-900 min-h-screen flex items-center justify-center">

<div class="w-full max-w-md px-6">

    {{-- Logo / Brand --}}
    <div class="text-center mb-8">
        @php $logo = \App\Models\BrandingSettings::instance()->getFirstMedia('logo'); @endphp
        @if($logo)
            <img src="{{ $logo->getUrl() }}" alt="{{ config('app.name') }}" class="h-12 mx-auto mb-4 object-contain">
        @else
            <h1 class="text-2xl font-bold tracking-wider uppercase text-white mb-1">{{ config('app.name') }}</h1>
        @endif
        <p class="text-slate-400 text-sm">Pannello di amministrazione</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-2xl p-8">

        <div class="text-center mb-6">
            <div class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900">Verifica email</h2>
            <p class="text-sm text-gray-500 mt-1">
                Abbiamo inviato un codice di 6 cifre alla tua email.<br>Il codice scade tra <strong>15 minuti</strong>.
            </p>
        </div>

        {{-- Status --}}
        @if(session('status'))
            <div class="mb-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3 text-center">
                {{ session('status') }}
            </div>
        @endif

        {{-- Verify form --}}
        <form method="POST" action="{{ route('admin.mfa.verify.submit') }}" x-data>
            @csrf
            <div class="mb-5">
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1.5">Codice di verifica</label>
                <input id="code"
                       name="code"
                       type="text"
                       inputmode="numeric"
                       pattern="[0-9]{6}"
                       maxlength="6"
                       autocomplete="one-time-code"
                       autofocus
                       value="{{ old('code') }}"
                       placeholder="000000"
                       class="block w-full text-center text-3xl font-bold tracking-[0.5em] px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition
                              {{ $errors->has('code') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                @error('code')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Verifica e accedi
            </button>
        </form>

        {{-- Resend --}}
        <div class="mt-5 pt-5 border-t border-gray-100 text-center">
            <p class="text-sm text-gray-500 mb-3">Non hai ricevuto il codice?</p>
            <form method="POST" action="{{ route('admin.mfa.resend') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Rinvia il codice
                </button>
            </form>
        </div>

        {{-- Back to login --}}
        <div class="mt-3 text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs text-gray-400 hover:text-gray-600 transition-colors">
                    Torna al login
                </button>
            </form>
        </div>

    </div>
</div>

</body>
</html>
