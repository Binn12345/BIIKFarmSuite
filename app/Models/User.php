<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'locale',
        'theme_background',
        'theme_accent',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'profile_picture',
        'profile_picture_mime_type',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('exclude_profile_picture', function (Builder $query): void {
            if ($query->getQuery()->columns !== null) {
                return;
            }

            $query->select([
                'users.id',
                'users.name',
                'users.email',
                'users.email_verified_at',
                'users.password',
                'users.remember_token',
                'users.created_at',
                'users.updated_at',
                'users.is_active',
                'users.phone',
                'users.address',
                'users.locale',
                'users.theme_background',
                'users.theme_accent',
            ]);
        });
    }

    public function profilePictureUrl(): string
    {
        return route('users.profile-picture', $this);
    }
}
