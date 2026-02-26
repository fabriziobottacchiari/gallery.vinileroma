<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SystemSettingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CheckStorage extends Command
{
    protected $signature = 'app:check-storage
                            {--disk= : Specific disk to test (default: reads from settings or falls back to default disk)}';

    protected $description = 'Verify that the configured storage disk is reachable and writable.';

    public function __construct(private readonly SystemSettingsService $settings)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $disk = $this->resolveDisk();

        $this->info("Testing storage disk: <comment>{$disk}</comment>");

        try {
            $this->testDisk($disk);
        } catch (Throwable $e) {
            $this->error("Disk [{$disk}] is NOT accessible: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info("Disk [{$disk}] is accessible and writable.");

        $this->displayDiskInfo($disk);

        return self::SUCCESS;
    }

    private function resolveDisk(): string
    {
        if ($this->option('disk')) {
            return (string) $this->option('disk');
        }

        $fromSettings = $this->settings->get('storage_disk');

        return $fromSettings ?? config('filesystems.default', 'local');
    }

    /**
     * Write a probe file, verify it exists, then remove it.
     *
     * @throws \RuntimeException
     */
    private function testDisk(string $disk): void
    {
        $probeFile = '.storage-check-probe-' . now()->timestamp;

        $storage = Storage::disk($disk);

        $storage->put($probeFile, 'ok');

        if (! $storage->exists($probeFile)) {
            throw new \RuntimeException('Probe file was written but could not be detected.');
        }

        $storage->delete($probeFile);
    }

    private function displayDiskInfo(string $disk): void
    {
        /** @var array<string, mixed> $diskConfig */
        $diskConfig = config("filesystems.disks.{$disk}", []);

        $driver = $diskConfig['driver'] ?? 'unknown';

        $this->table(['Key', 'Value'], [
            ['disk', $disk],
            ['driver', $driver],
            ['bucket / root', $diskConfig['bucket'] ?? $diskConfig['root'] ?? '—'],
            ['region', $diskConfig['region'] ?? '—'],
            ['endpoint', $diskConfig['endpoint'] ?? '—'],
        ]);
    }
}
