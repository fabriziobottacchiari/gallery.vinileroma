@extends('layouts.public')

@section('title', 'Accedi — Vinile Roma Gallery')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-md">

        {{-- Logo / Brand --}}
        <div class="text-center mb-8">
            <a href="{{ route('public.events.index') }}" class="inline-block text-2xl font-bold tracking-tight text-white">
                Vinile Roma Gallery
            </a>
            <p class="mt-2 text-zinc-400 text-sm">Accedi per visualizzare le gallerie fotografiche</p>
        </div>

        {{-- Flash messages --}}
        @if(session('info'))
            <div class="mb-4 rounded-lg bg-indigo-900/50 border border-indigo-700 text-indigo-200 px-4 py-3 text-sm">
                {{ session('info') }}
            </div>
        @endif
        @if(session('success'))
            <div class="mb-4 rounded-lg bg-emerald-900/50 border border-emerald-700 text-emerald-200 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-8 shadow-2xl">
            <form method="POST" action="{{ route('gallery.login') }}">
                @csrf

                <div class="space-y-5">
                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-zinc-300 mb-1.5">Email</label>
                        <input id="email" name="email" type="email" autocomplete="email"
                               value="{{ old('email') }}"
                               class="w-full bg-zinc-800 border {{ $errors->has('email') ? 'border-red-500' : 'border-zinc-700' }} rounded-lg px-4 py-2.5 text-white text-sm placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-sm font-medium text-zinc-300">Password</label>
                            <a href="{{ route('gallery.password.request') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Password dimenticata?</a>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password"
                               class="w-full bg-zinc-800 border {{ $errors->has('password') ? 'border-red-500' : 'border-zinc-700' }} rounded-lg px-4 py-2.5 text-white text-sm placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('password') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Remember --}}
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-zinc-600 bg-zinc-800 text-indigo-500 focus:ring-indigo-500">
                        <span class="text-sm text-zinc-400">Ricordami</span>
                    </label>

                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-zinc-900">
                        Accedi
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center mt-6 text-sm text-zinc-500">
            Non hai un account?
            <a href="{{ route('gallery.register') }}" class="text-indigo-400 hover:text-indigo-300 font-medium">Registrati</a>
        </p>

    </div>
</div>
@endsection
