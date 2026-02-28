<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\BrandingSettings;
use App\Models\PhotoUpload;
use App\Services\SystemSettingsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Throwable;

class ProcessEventPhoto implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Maximum retry attempts before marking as failed.
     */
    public int $tries = 3;

    /**
     * Seconds before a running job is considered timed out.
     */
    public int $timeout = 180;

    /**
     * Seconds to wait before retrying after a failure.
     */
    public int $backoff = 15;

    public function __construct(
        public readonly int $photoUploadId,
    ) {}

    public function handle(SystemSettingsService $settings): void
    {
        // Raise memory limit — raw bitmaps for large JPEGs can exceed 200 MB
        ini_set('memory_limit', '512M');

        $upload = PhotoUpload::find($this->photoUploadId);

        if ($upload === null || $upload->status === 'completed') {
            return;
        }

        $upload->update(['status' => 'processing']);

        $event    = $upload->event;
        $tempPath = Storage::disk('local')->path($upload->temp_path);

        if (! file_exists($tempPath)) {
            throw new \RuntimeException("File temporaneo non trovato per: {$upload->original_filename}");
        }

        // ── Preserve original file ───────────────────────────────────────────
        $ext          = strtolower(pathinfo($upload->original_filename, PATHINFO_EXTENSION)) ?: 'jpg';
        $origPrefix   = Str::random(8);
        $origBaseName = Str::slug(pathinfo($upload->original_filename, PATHINFO_FILENAME));
        $originalDir  = 'originals/' . $event->id;
        $originalPath = $originalDir . '/' . $origPrefix . '_' . $origBaseName . '.' . $ext;

        Storage::disk('local')->makeDirectory($originalDir);
        Storage::disk('local')->put($originalPath, file_get_contents($tempPath));

        $upload->update(['original_path' => $originalPath]);

        $manager = new ImageManager(new GdDriver());
        $image   = $manager->read($tempPath);

        $originalWidth  = $image->width();
        $originalHeight = $image->height();

        // ── Watermark ────────────────────────────────────────────────────────
        if ($event->has_watermark) {
            $logoMedia = BrandingSettings::instance()->getFirstMedia('logo');

            if ($logoMedia !== null) {
                try {
                    $watermark  = $manager->read($logoMedia->getPath());
                    $wmWidth    = max(30, (int) ($originalWidth * 0.15));
                    $watermark->scaleDown(width: $wmWidth);

                    // 4th param = x-offset, 5th = y-offset, 6th = opacity (0-100)
                    $image->place($watermark, 'bottom-right', 20, 20, 70);

                    unset($watermark);
                } catch (Throwable $e) {
                    Log::warning("Watermark ignorato per «{$upload->original_filename}»: {$e->getMessage()}");
                }
            }
        }

        // ── Naming: YYYY-MM-DD-{slug}-{hash}.webp ────────────────────────────
        $hash     = Str::random(8);
        $filename = $event->event_date->format('Y-m-d') . '-' . $event->slug . '-' . $hash . '.webp';

        // ── Encode as WebP (quality 95 for master) ──────────────────────────
        $webpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        $image->toWebp(quality: 95)->save($webpPath);

        unset($image);
        gc_collect_cycles();

        // ── Add to Spatie gallery collection ─────────────────────────────────
        $disk = $settings->get('storage_disk', config('filesystems.default', 'public'));

        $media = $event
            ->addMedia($webpPath)
            ->usingFileName($filename)
            ->withCustomProperties([
                'original_width'  => $originalWidth,
                'original_height' => $originalHeight,
            ])
            ->toMediaCollection('gallery', $disk);

        gc_collect_cycles();

        // ── Auto-cover: set if none is defined yet ───────────────────────────
        if ($event->cover_media_id === null) {
            $event->update(['cover_media_id' => $media->id]);
        }

        // ── Clean up original temp file ──────────────────────────────────────
        if ($upload->temp_path && Storage::disk('local')->exists($upload->temp_path)) {
            Storage::disk('local')->delete($upload->temp_path);
        }

        $upload->update([
            'status'    => 'completed',
            'media_id'  => $media->id,
            'temp_path' => null,
        ]);
    }

    /**
     * Called after all retries are exhausted.
     */
    public function failed(Throwable $e): void
    {
        PhotoUpload::find($this->photoUploadId)?->update([
            'status'        => 'failed',
            'error_message' => mb_substr($e->getMessage(), 0, 500),
        ]);

        Log::error("ProcessEventPhoto fallito [upload #{$this->photoUploadId}]", [
            'error' => $e->getMessage(),
        ]);
    }
}
