<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class GalleryUser extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'birth_year',
        'gender',
        'instagram_handle',
        'privacy_accepted',
        'newsletter_consent',
        'marketing_consent',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'privacy_accepted'   => 'boolean',
            'newsletter_consent' => 'boolean',
            'marketing_consent'  => 'boolean',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(GalleryUserFavorite::class, 'gallery_user_id');
    }

    public static function genderLabel(string $value): string
    {
        return match ($value) {
            'male'              => 'Uomo',
            'female'            => 'Donna',
            'prefer_not_to_say' => 'Preferisco non specificare',
            default             => $value,
        };
    }
}
