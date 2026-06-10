<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Cache;

class PlatformSettingsService
{
    private const CACHE_KEY = 'platform_settings_all';

    private const TTL = 300;

    /** @var array<string, mixed>|null */
    private static ?array $defaults = null;

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        if (self::$defaults === null) {
            self::$defaults = [
                'max_sites_per_user' => 2,
                'retention_days' => (int) config('analytics.retention_days', 365),
                'rollup_enabled' => (bool) config('analytics.rollup_enabled', true),
                'collect_rate_limit' => 120,
                'registration_enabled' => true,
                'default_date_range' => 'last_30_days',
                'default_analytics_range' => 30,
                'maintenance_mode' => false,
                'app_display_name' => config('app.name', 'VibeMetrics'),
                'support_email' => null,
                'brand_primary_color' => '#4f46e5',
                'email_logo_same_as_site' => true,
                'transactional_emails_enabled' => true,
                'email_welcome_enabled' => true,
                'email_password_changed_enabled' => true,
                'email_account_deactivated_enabled' => true,
                'site_logo_path' => null,
                'email_logo_path' => null,
                'favicon_path' => null,
            ];
        }

        return self::$defaults;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        if (array_key_exists($key, $all)) {
            return $all[$key];
        }

        return $default ?? (self::defaults()[$key] ?? null);
    }

    public function getInt(string $key, int $default = 0): int
    {
        return (int) $this->get($key, $default);
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::TTL, function () {
            $stored = PlatformSetting::query()->pluck('value', 'key')->all();
            $merged = self::defaults();

            foreach ($stored as $key => $value) {
                $merged[$key] = $this->decodeValue($value);
            }

            return $merged;
        });
    }

    public function set(string $key, mixed $value): void
    {
        PlatformSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $this->encodeValue($value), 'updated_at' => now()]
        );

        Cache::forget(self::CACHE_KEY);
    }

    /** @param  array<string, mixed>  $settings */
    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
    }

    private function encodeValue(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    private function decodeValue(string $value): mixed
    {
        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }
}
