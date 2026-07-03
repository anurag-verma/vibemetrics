<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Site extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'name',
        'domain',
        'is_paused',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_paused' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Site $site): void {
            if (empty($site->tracking_id)) {
                $site->tracking_id = (string) Str::uuid();
            }
        });

        static::updated(function (Site $site): void {
            if ($site->wasChanged('tracking_id')) {
                $original = $site->getOriginal('tracking_id');

                if (is_string($original) && $original !== '') {
                    Cache::forget("site:{$original}");
                }
            }

            if ($site->wasChanged(['is_paused', 'tracking_id'])) {
                Cache::forget("site:{$site->tracking_id}");
            }
        });

        static::deleted(function (Site $site): void {
            Cache::forget("site:{$site->tracking_id}");
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pageViews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PageView::class);
    }

    public function dailyStats(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DailyStat::class);
    }

    public function goals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Goal::class);
    }

    public function isActive(): bool
    {
        return ! $this->is_paused;
    }

    public function trackingSnippet(): string
    {
        $host = rtrim(config('app.url'), '/');

        return sprintf(
            '<script defer data-website-id="%s" data-api-host="%s" src="%s/js/tracker.js"></script>',
            $this->tracking_id,
            $host,
            $host
        );
    }
}
