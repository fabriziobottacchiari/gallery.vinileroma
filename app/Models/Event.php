<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Event extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'event_date',
        'is_private',
        'has_watermark',
        'status',
        'cover_media_id',
    ];

    protected function casts(): array
    {
        return [
            'event_date'    => 'date',
            'is_private'    => 'boolean',
            'has_watermark' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Route parameters for the public-facing URL:  /evento/{year}/{month}/{day}/{slug}
     *
     * @return array{year: string, month: string, day: string, slug: string}
     */
    public function publicRouteParams(): array
    {
        return [
            'year'  => $this->event_date->format('Y'),
            'month' => $this->event_date->format('m'),
            'day'   => $this->event_date->format('d'),
            'slug'  => $this->slug,
        ];
    }

    public function photoUploads(): HasMany
    {
        return $this->hasMany(PhotoUpload::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
    }

    /**
     * Spatie media conversions for the gallery collection.
     *
     * Conversions are nonQueued() so they run inline inside ProcessEventPhoto,
     * keeping each job fully atomic (upload → watermark → store → convert).
     *
     * Fit::Max resizes to fit within the bounding box without upscaling;
     * this correctly implements "lato lungo massimo N px" from the spec.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Max, 600, 600)
            ->format('webp')
            ->quality(70)
            ->nonQueued()
            ->performOnCollections('gallery');

        $this->addMediaConversion('preview')
            ->fit(Fit::Max, 1200, 1200)
            ->format('webp')
            ->quality(80)
            ->nonQueued()
            ->performOnCollections('gallery');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_private', false);
    }
}
