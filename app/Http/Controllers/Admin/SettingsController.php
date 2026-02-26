<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandingSettings;
use App\Services\SystemSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        private readonly SystemSettingsService $settings,
    ) {}

    public function edit(): View
    {
        $branding = BrandingSettings::instance();
        $logo     = $branding->getFirstMedia('logo');

        $data = [
            'branding'     => $branding,
            'logo'         => $logo,
            'custom_css'   => $this->settings->get('custom_css', ''),
            'storage_disk' => $this->settings->get('storage_disk', 'local'),
            's3_key'       => $this->settings->get('s3_key', ''),
            's3_secret'    => $this->settings->get('s3_secret', ''),
            's3_bucket'    => $this->settings->get('s3_bucket', ''),
            's3_region'    => $this->settings->get('s3_region', ''),
            's3_endpoint'  => $this->settings->get('s3_endpoint', ''),
            'do_key'       => $this->settings->get('do_key', ''),
            'do_secret'    => $this->settings->get('do_secret', ''),
            'do_bucket'    => $this->settings->get('do_bucket', ''),
            'do_region'    => $this->settings->get('do_region', ''),
            'do_endpoint'  => $this->settings->get('do_endpoint', ''),
        ];

        return view('admin.settings.edit', $data);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'logo'         => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:4096'],
            'custom_css'   => ['nullable', 'string', 'max:65535'],
            'storage_disk' => ['nullable', 'in:local,public,s3,digitalocean'],
            's3_key'       => ['nullable', 'string', 'max:255'],
            's3_secret'    => ['nullable', 'string', 'max:255'],
            's3_bucket'    => ['nullable', 'string', 'max:255'],
            's3_region'    => ['nullable', 'string', 'max:255'],
            's3_endpoint'  => ['nullable', 'string', 'max:255'],
            'do_key'       => ['nullable', 'string', 'max:255'],
            'do_secret'    => ['nullable', 'string', 'max:255'],
            'do_bucket'    => ['nullable', 'string', 'max:255'],
            'do_region'    => ['nullable', 'string', 'max:255'],
            'do_endpoint'  => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('logo')) {
            BrandingSettings::instance()
                ->addMediaFromRequest('logo')
                ->toMediaCollection('logo');
        }

        $settingKeys = [
            'custom_css', 'storage_disk',
            's3_key', 's3_secret', 's3_bucket', 's3_region', 's3_endpoint',
            'do_key', 'do_secret', 'do_bucket', 'do_region', 'do_endpoint',
        ];

        foreach ($settingKeys as $key) {
            if ($request->has($key)) {
                $this->settings->set($key, $request->input($key));
            }
        }

        return back()->with('success', 'Impostazioni salvate con successo.');
    }
}
