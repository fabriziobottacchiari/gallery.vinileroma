<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class SystemSettingsService
{
    private const CACHE_KEY = 'system_settings';

    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Keys whose values are stored encrypted at rest.
     * These are cloud storage credentials that must never appear in DB backups as plaintext.
     */
    private const ENCRYPTED_KEYS = [
        's3_secret',
        'do_secret',
        's3_key',
        'do_key',
    ];

    /**
     * Retrieve a single setting value by key (decrypted if necessary).
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /**
     * Retrieve all settings as a key-value array.
     * Encrypted values are decrypted transparently.
     *
     * @return array<string, string|null>
     */
    public function all(): array
    {
        $raw = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function (): array {
            return Setting::all()->pluck('value', 'key')->all();
        });

        $result = [];
        foreach ($raw as $key => $value) {
            if ($value !== null && in_array($key, self::ENCRYPTED_KEYS, true)) {
                try {
                    $result[$key] = Crypt::decryptString($value);
                } catch (DecryptException) {
                    // Value was stored before encryption was added — return as-is
                    // On next save, it will be re-encrypted automatically
                    $result[$key] = $value;
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Persist a setting value (encrypted if it's a sensitive key) and invalidate the cache.
     */
    public function set(string $key, mixed $value): void
    {
        $stored = $value;

        if ($value !== null && in_array($key, self::ENCRYPTED_KEYS, true)) {
            $stored = Crypt::encryptString((string) $value);
        } elseif ($value !== null) {
            $stored = (string) $value;
        }

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $stored],
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
