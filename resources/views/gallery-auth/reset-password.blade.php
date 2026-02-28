@extends('layouts.public')

@section('title', 'Reimposta password — Vinile Roma Gallery')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-white">Nuova password</h1>
            <p class="mt-2 text-zinc-400 text-sm">Scegli una nuova password per il tuo account.</p>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-8 shadow-2xl">
            <form method="POST" action="{{ route('gallery.password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-zinc-300 mb-1.5">Email</label>
                        <input id="email" name="email" type="email" autocomplete="email"
                               value="{{ old('email', $email) }}"
                               class="w-full bg-zinc-800 border {{ $errors->has('email') ? 'border-red-500' : 'border-zinc-700' }} rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-zinc-300 mb-1.5">Nuova password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password"
                               class="w-full bg-zinc-800 border {{ $errors->has('password') ? 'border-red-500' : 'border-zinc-700' }} rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('password') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-zinc-300 mb-1.5">Conferma password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
                        Reimposta password
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
