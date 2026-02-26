@extends('layouts.admin')

@section('title', 'Impostazioni')

@section('content')
<div class="max-w-3xl space-y-6">

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf @method('PATCH')

        {{-- ── Branding ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-800 text-sm">Branding</h2>
                <p class="text-xs text-gray-500 mt-0.5">Logo usato nell'header e come sorgente del watermark sulle foto.</p>
            </div>
            <div class="p-6 space-y-5">

                {{-- Current logo --}}
                @if($logo)
                <div>
                    <p class="text-xs font-medium text-gray-600 mb-2">Logo attuale</p>
                    <div class="inline-flex items-center gap-4 bg-slate-900 rounded-xl px-5 py-4">
                        <img src="{{ $logo->getUrl() }}" alt="Logo" class="h-12 w-auto object-contain">
                        <span class="text-xs text-slate-400">{{ $logo->file_name }} ({{ round($logo->size / 1024) }} KB)</span>
                    </div>
                </div>
                @endif

                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $logo ? 'Sostituisci logo' : 'Carica logo' }}
                        <span class="ml-1 text-xs text-gray-400 font-normal">PNG, JPG, WebP, SVG — max 4 MB</span>
                    </label>
                    <input id="logo" name="logo" type="file" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition">
                    @error('logo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- ── Custom CSS ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-800 text-sm">CSS personalizzato</h2>
                <p class="text-xs text-gray-500 mt-0.5">Viene iniettato nel frontend pubblico della galleria.</p>
            </div>
            <div class="p-6">
                <textarea id="custom_css" name="custom_css" rows="10"
                          placeholder="/* Il tuo CSS personalizzato */"
                          class="w-full font-mono text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 resize-y bg-gray-50 text-gray-800">{{ old('custom_css', $custom_css) }}</textarea>
                @error('custom_css') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- ── Storage ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-800 text-sm">Storage</h2>
                <p class="text-xs text-gray-500 mt-0.5">La configurazione sovrascrive a runtime i valori del file <code class="font-mono">.env</code>.</p>
            </div>
            <div class="p-6 space-y-5" x-data="{ disk: '{{ old('storage_disk', $storage_disk) }}' }">

                {{-- Active disk --}}
                <div>
                    <label for="storage_disk" class="block text-sm font-medium text-gray-700 mb-1">Disco attivo</label>
                    <select id="storage_disk" name="storage_disk" x-model="disk"
                            class="border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        <option value="local">Local (server)</option>
                        <option value="public">Public (server, pubblico)</option>
                        <option value="s3">Amazon S3</option>
                        <option value="digitalocean">DigitalOcean Spaces</option>
                    </select>
                </div>

                {{-- S3 fields --}}
                <div x-show="disk === 's3'" x-cloak class="space-y-4 rounded-xl border border-gray-200 p-5 bg-gray-50">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Credenziali Amazon S3</p>
                    @foreach([
                        ['s3_key',      'Access Key ID',     'text'],
                        ['s3_secret',   'Secret Access Key', 'password'],
                        ['s3_bucket',   'Bucket',            'text'],
                        ['s3_region',   'Region',            'text'],
                        ['s3_endpoint', 'Endpoint (opz.)',   'url'],
                    ] as [$name, $label, $type])
                    <div>
                        <label for="{{ $name }}" class="block text-xs font-medium text-gray-600 mb-1">{{ $label }}</label>
                        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
                               value="{{ old($name, $$name) }}"
                               class="w-full font-mono text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    @endforeach
                </div>

                {{-- DigitalOcean fields --}}
                <div x-show="disk === 'digitalocean'" x-cloak class="space-y-4 rounded-xl border border-gray-200 p-5 bg-gray-50">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Credenziali DigitalOcean Spaces</p>
                    @foreach([
                        ['do_key',      'Spaces Key',        'text'],
                        ['do_secret',   'Spaces Secret',     'password'],
                        ['do_bucket',   'Bucket / Space',    'text'],
                        ['do_region',   'Region (es. ams3)', 'text'],
                        ['do_endpoint', 'Endpoint CDN (opz.)','url'],
                    ] as [$name, $label, $type])
                    <div>
                        <label for="{{ $name }}" class="block text-xs font-medium text-gray-600 mb-1">{{ $label }}</label>
                        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
                               value="{{ old($name, $$name) }}"
                               class="w-full font-mono text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    @endforeach
                </div>

            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Salva impostazioni
            </button>
        </div>

    </form>
</div>
@endsection
