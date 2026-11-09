<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasLibraryMedia;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasLibraryMedia, HasRoles, Notifiable;

    public function registerMediaCollections(): void
    {
        // Account pictures live in their own `users` library and never mix
        // with catalogue or CMS uploads.
        $this->addMediaCollection('avatar')->singleFile();
    }

    public function avatarUrl(): ?string
    {
        return $this->firstMediaUrl('avatar') ?? $this->legacyMediaUrl($this->avatar);
    }

    public function isAdmin(): bool
    {
        return $this->is_admin || $this->hasRole('admin');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'phone',
        'address',
        'country',
        'state',
        'city',
        'postal_code',
        'avatar',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'banned' => 'boolean',
        'balance' => 'float',
    ];

    // Relationships
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
