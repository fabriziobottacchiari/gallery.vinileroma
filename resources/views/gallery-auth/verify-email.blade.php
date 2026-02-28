@extends('layouts.public')

@section('title', 'Verifica email — Vinile Roma Gallery')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-md text-center">

        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-900/40 border border-indigo-700 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-white mb-2">Verifica la tua email</h1>
        <p class="text-zinc-400 text-sm leading-relaxed mb-8">
            Abbiamo inviato un link di verifica a <strong class="text-zinc-200">{{ auth('gallery')->user()->email }}</strong>.<br>
            Clicca sul link per attivare il tuo account e accedere alle gallerie.
        </p>

        @if(session('success'))
            <div class="mb-6 rounded-lg bg-emerald-900/50 border border-emerald-700 text-emerald-200 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('gallery.verification.send') }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reinvia email di verifica
            </button>
        </form>

        <form method="POST" action="{{ route('gallery.logout') }}" class="mt-6">
            @csrf
            <button type="submit" class="text-sm text-zinc-500 hover:text-zinc-300 transition-colors">
                Esci e usa un altro account
            </button>
        </form>

    </div>
</div>
@endsection
