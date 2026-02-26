<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SystemSettingsService
{
    private const CACHE_KEY = 'system_settings';

    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Retrieve a single setting value by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /**
     * Retrieve all settings as a key-value array, with caching.
     *
     * @return array<string, string|null>
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function (): array {
            return Setting::all()->pluck('value', 'key')->all();
        });
    }

    /**
     * Persist a setting value and invalidate the cache.
     */
    public function set(string $key, mixed $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value !== null ? (string) $value : null],
        );

        $this->flush();
    }

    /**
     * Remove a setting and invalidate the cache.
     */
    public function forget(string $key): void
    {
        Setting::where('key', $key)->delete();
        $this->flush();
    }

    /**
     * Invalidate the settings cache.
     */
    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
