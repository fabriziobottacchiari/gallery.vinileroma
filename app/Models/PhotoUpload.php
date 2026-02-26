<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoUpload extends Model
{
    protected $fillable = [
        'event_id',
        'batch_uuid',
        'original_filename',
        'temp_path',
        'status',
        'error_message',
        'media_id',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
