<?php

namespace App\Models;

use App\Support\AnalyticsDateRange;
use App\Support\TimezoneList;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'timezone',
        'default_date_range',
        'site_limit',
        'password',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'site_limit' => 'integer',
        ];
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function isLastAdmin(): bool
    {
        return $this->isAdmin()
            && static::query()->where('is_admin', true)->count() <= 1;
    }

    public function defaultHomeRoute(): string
    {
        return $this->isAdmin()
            ? route('admin.dashboard', absolute: false)
            : route('dashboard', absolute: false);
    }

    public function preferredTimezone(): string
    {
        return TimezoneList::resolve($this->timezone);
    }

    public function preferredDateRange(): string
    {
        return AnalyticsDateRange::resolvePreset($this->default_date_range);
    }
}
