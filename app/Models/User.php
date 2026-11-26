<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    // Soft deletes: a deleted user disappears from lists and can't sign in, but visit history keeps them.
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'membership_code',
        'phone',
        'type',
        'password',
        'font_size',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'font_size' => 'integer',
    ];

    public const FILTERS_CACHE_KEY = 'settings.filters';

    protected static function booted(): void
    {
        $forget = fn () => Cache::forget(self::FILTERS_CACHE_KEY);

        // The cached filters only hold name/membership_code grouped by type. save() fires "saved"
        // even when nothing changed (the SSO provisioner saves the user on every raw-token API
        // request), so only drop the cache when one of those columns actually changed.
        static::saved(function (self $user) use ($forget) {
            if ($user->wasRecentlyCreated || $user->wasChanged(['name', 'membership_code', 'type'])) {
                $forget();
            }
        });
        static::deleted($forget);
    }

    protected function membershipCode(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value === null ? null : strtoupper($value),
        );
    }
}
