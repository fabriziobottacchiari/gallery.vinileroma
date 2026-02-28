@extends('layouts.public')

@section('title', 'Password dimenticata — Vinile Roma Gallery')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <a href="{{ route('gallery.login') }}" class="inline-flex items-center gap-1.5 text-sm text-zinc-500 hover:text-zinc-300 transition-colors mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Torna al login
            </a>
            <h1 class="text-2xl font-bold text-white">Password dimenticata?</h1>
            <p class="mt-2 text-zinc-400 text-sm">Inserisci la tua email e ti invieremo un link per reimpostare la password.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg bg-emerald-900/50 border border-emerald-700 text-emerald-200 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-8 shadow-2xl">
            <form method="POST" action="{{ route('gallery.password.request') }}">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-zinc-300 mb-1.5">Email</label>
                        <input id="email" name="email" type="email" autocomplete="email"
                               value="{{ old('email') }}"
                               class="w-full bg-zinc-800 border {{ $errors->has('email') ? 'border-red-500' : 'border-zinc-700' }} rounded-lg px-4 py-2.5 text-white text-sm placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
                        Invia link di reset
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
