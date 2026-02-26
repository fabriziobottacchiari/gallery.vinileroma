<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class BrandingSettings extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'branding_settings';

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml']);
    }

    /**
     * Return the singleton instance, creating the row if it doesn't exist.
     */
    public static function instance(): self
    {
        return static::first() ?? tap(new static(), fn (self $m) => $m->save());
    }
}
