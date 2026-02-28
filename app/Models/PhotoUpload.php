<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PhotoUpload extends Model
{
    protected $fillable = [
        'event_id',
        'batch_uuid',
        'original_filename',
        'temp_path',
        'original_path',
        'status',
        'error_message',
        'media_id',
        'is_hidden',
    ];

    protected function casts(): array
    {
        return [
            'is_hidden' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        // Clean up leftover temp and original files when the record is deleted
        static::deleting(function (PhotoUpload $upload): void {
            if ($upload->temp_path) {
                Storage::disk('local')->delete($upload->temp_path);
            }
            if ($upload->original_path) {
                Storage::disk('local')->delete($upload->original_path);
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
