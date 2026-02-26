<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
    @stack('head')
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900" x-data="adminApp()" x-init="init()">

{{-- ── Mobile sidebar backdrop ──────────────────────────────────────────── --}}
<div x-show="sidebarOpen"
     x-cloak
     @click="sidebarOpen = false"
     class="fixed inset-0 z-20 bg-black/40 lg:hidden"
     x-transition:enter="transition-opacity duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"></div>

{{-- ── Mobile sidebar ────────────────────────────────────────────────────── --}}
<div x-show="sidebarOpen"
     x-cloak
     class="fixed inset-y-0 left-0 z-30 flex lg:hidden"
     x-transition:enter="transition-transform duration-300 ease-out"
     x-transition:enter-start="-translate-x-full"
     x-transition:enter-end="translate-x-0"
     x-transition:leave="transition-transform duration-300 ease-in"
     x-transition:leave-start="translate-x-0"
     x-transition:leave-end="-translate-x-full">
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col shadow-xl overflow-y-auto">
        @include('layouts.partials.admin-sidebar-content')
    </aside>
</div>

{{-- ── Toast container ──────────────────────────────────────────────────── --}}
<div class="fixed top-4 right-4 z-50 w-80 space-y-2 pointer-events-none" aria-live="polite">
    <template x-for="toast in toasts" :key="toast.id">
        <div class="pointer-events-auto flex items-start gap-3 bg-white rounded-xl shadow-lg border border-gray-100 px-4 py-3.5 text-sm"
             x-show="toast.visible"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-4">

            <span class="flex-shrink-0 mt-0.5"
                  :class="{
                      'text-emerald-500': toast.type === 'success',
                      'text-red-500':     toast.type === 'error',
                      'text-amber-500':   toast.type === 'warning'
                  }">
                <template x-if="toast.type === 'success'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </template>
                <template x-if="toast.type === 'warning'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </template>
            </span>

            <p x-text="toast.message" class="flex-1 text-gray-700 leading-snug"></p>

            <button @click="removeToast(toast.id)"
                    class="flex-shrink-0 text-gray-300 hover:text-gray-500 transition-colors mt-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>

{{-- ── Delete confirmation modal ────────────────────────────────────────── --}}
<div x-show="deleteModal.visible"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click.self="deleteModal.visible = false">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        <div class="flex items-start gap-4 mb-5">
            <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-gray-900">Conferma eliminazione</h3>
                <p x-text="deleteModal.message" class="text-sm text-gray-500 mt-1 leading-relaxed"></p>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <button @click="deleteModal.visible = false"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">
                Annulla
            </button>
            <button @click="submitDelete()"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors">
                Elimina
            </button>
        </div>
    </div>
</div>

{{-- ── App shell ────────────────────────────────────────────────────────── --}}
<div class="flex h-screen overflow-hidden">

    {{-- Desktop sidebar (always visible on lg+) --}}
    <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:flex-shrink-0 bg-white border-r border-gray-200">
        @include('layouts.partials.admin-sidebar-content')
    </aside>

    {{-- Main area --}}
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">

        {{-- Top bar --}}
        <header class="h-16 bg-white border-b border-gray-200 flex items-center px-4 sm:px-6 gap-3 flex-shrink-0">
            <button @click="sidebarOpen = true"
                    class="lg:hidden h-9 w-9 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition-colors"
                    aria-label="Apri menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <h1 class="text-base font-semibold text-gray-800 flex-1 truncate">@yield('title', 'Pannello Admin')</h1>
            @yield('header-actions')
        </header>

        {{-- Validation errors (inline banner) --}}
        @if($errors->any())
            <div class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>

    </div>
</div>

@yield('scripts')

<script>
function adminApp() {
    return {
        sidebarOpen: false,
        toasts:      [],
        nextId:      0,
        deleteModal: { visible: false, message: '', formId: null },

        init() {
            const f = @json([
                'success' => session('success'),
                'warning' => session('warning'),
                'error'   => session('error'),
            ]);
            if (f.success) this.addToast('success', f.success);
            if (f.warning) this.addToast('warning', f.warning);
            if (f.error)   this.addToast('error',   f.error);
        },

        addToast(type, message) {
            const id = this.nextId++;
            this.toasts.push({ id, type, message, visible: true });
            setTimeout(() => this.removeToast(id), 5000);
        },

        removeToast(id) {
            const t = this.toasts.find(t => t.id === id);
            if (t) t.visible = false;
            setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 300);
        },

        confirmDelete(formId, message) {
            this.deleteModal.formId  = formId;
            this.deleteModal.message = message;
            this.deleteModal.visible = true;
        },

        submitDelete() {
            if (this.deleteModal.formId) {
                document.getElementById(this.deleteModal.formId)?.submit();
            }
        },
    };
}
</script>
</body>
</html>
