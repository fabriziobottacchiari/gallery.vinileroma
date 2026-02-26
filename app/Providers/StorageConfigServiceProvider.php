<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\SystemSettingsService;
use Illuminate\Support\ServiceProvider;
use Throwable;

class StorageConfigServiceProvider extends ServiceProvider
{
    /**
     * Keys in the settings table that map to S3 / DigitalOcean Spaces credentials.
     *
     * @var array<string, string>
     */
    private const S3_SETTING_MAP = [
        'key'      => 's3_key',
        'secret'   => 's3_secret',
        'bucket'   => 's3_bucket',
        'region'   => 's3_region',
        'endpoint' => 's3_endpoint',
    ];

    private const DO_SETTING_MAP = [
        'key'      => 'do_key',
        'secret'   => 'do_secret',
        'bucket'   => 'do_bucket',
        'region'   => 'do_region',
        'endpoint' => 'do_endpoint',
    ];

    public function boot(): void
    {
        try {
            /** @var SystemSettingsService $settings */
            $settings = $this->app->make(SystemSettingsService::class);

            $this->applyDiskConfig('s3', self::S3_SETTING_MAP, $settings);
            $this->applyDiskConfig('digitalocean', self::DO_SETTING_MAP, $settings);
        } catch (Throwable) {
            // Database may not be available yet (e.g. during initial migrations).
            // Silently fall back to the values defined in filesystems.php / .env.
        }
    }

    /**
     * Overwrite a filesystem disk configuration at runtime using values from
     * the settings table, skipping keys whose setting value is null / empty.
     *
     * @param  array<string, string>  $settingMap  Map of config-key => setting-key
     */
    private function applyDiskConfig(
        string $disk,
        array $settingMap,
        SystemSettingsService $settings,
    ): void {
        foreach ($settingMap as $configKey => $settingKey) {
            $value = $settings->get($settingKey);

            if ($value !== null && $value !== '') {
                config(["filesystems.disks.{$disk}.{$configKey}" => $value]);
            }
        }
    }
}
