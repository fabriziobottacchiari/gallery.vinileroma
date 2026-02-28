<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryUserFavorite extends Model
{
    protected $fillable = [
        'gallery_user_id',
        'media_id',
        'event_id',
        'photo_upload_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(GalleryUser::class, 'gallery_user_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function photoUpload(): BelongsTo
    {
        return $this->belongsTo(PhotoUpload::class);
    }
}
