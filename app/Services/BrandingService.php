<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandingService
{
    public const ASSET_SITE_LOGO = 'site_logo';

    public const ASSET_EMAIL_LOGO = 'email_logo';

    public const ASSET_FAVICON = 'favicon';

    public function __construct(
        private PlatformSettingsService $settings,
    ) {}

    public function displayName(): string
    {
        $name = $this->settings->get('app_display_name');

        if (is_string($name) && trim($name) !== '') {
            return trim($name);
        }

        return (string) config('app.name', 'VibeMetrics');
    }

    public function supportEmail(): ?string
    {
        $email = $this->settings->get('support_email');

        return is_string($email) && $email !== '' ? $email : null;
    }

    public function primaryColor(): string
    {
        $color = $this->settings->get('brand_primary_color');

        if (is_string($color) && preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return $color;
        }

        return '#4f46e5';
    }

    public function emailLogoSameAsSite(): bool
    {
        return $this->settings->getBool('email_logo_same_as_site', true);
    }

    public function siteLogoUrl(): string
    {
        return $this->assetUrl('site_logo_path', 'images/vibemetrics.png');
    }

    public function emailLogoUrl(): string
    {
        if ($this->emailLogoSameAsSite()) {
            return $this->siteLogoUrl();
        }

        return $this->assetUrl('email_logo_path', 'images/vibemetrics.png');
    }

    public function faviconUrl(): string
    {
        return $this->assetUrl('favicon_path', 'images/vibemetrics.png');
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'appName' => $this->displayName(),
            'supportEmail' => $this->supportEmail(),
            'primaryColor' => $this->primaryColor(),
            'siteLogoUrl' => $this->siteLogoUrl(),
            'emailLogoUrl' => $this->emailLogoUrl(),
            'faviconUrl' => $this->faviconUrl(),
            'emailLogoSameAsSite' => $this->emailLogoSameAsSite(),
            'hasCustomSiteLogo' => $this->hasCustomAsset('site_logo_path'),
            'hasCustomEmailLogo' => $this->hasCustomAsset('email_logo_path'),
            'hasCustomFavicon' => $this->hasCustomAsset('favicon_path'),
        ];
    }

    public function storeUpload(UploadedFile $file, string $type): void
    {
        $pathKey = $this->pathKeyForType($type);
        $this->deleteStoredFile($this->settings->get($pathKey));

        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'png';
        $filename = Str::slug($type).'.'.strtolower($extension);
        $storedPath = $file->storeAs('branding', $filename, 'public');

        $this->settings->set($pathKey, $storedPath);
    }

    public function removeAsset(string $type): void
    {
        $pathKey = $this->pathKeyForType($type);
        $this->deleteStoredFile($this->settings->get($pathKey));
        $this->settings->set($pathKey, null);
    }

    private function pathKeyForType(string $type): string
    {
        return match ($type) {
            self::ASSET_SITE_LOGO => 'site_logo_path',
            self::ASSET_EMAIL_LOGO => 'email_logo_path',
            self::ASSET_FAVICON => 'favicon_path',
            default => throw new \InvalidArgumentException("Unknown branding asset type: {$type}"),
        };
    }

    private function assetUrl(string $pathKey, string $publicFallback): string
    {
        $path = $this->settings->get($pathKey);

        if (is_string($path) && $path !== '' && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return asset($publicFallback);
    }

    private function hasCustomAsset(string $pathKey): bool
    {
        $path = $this->settings->get($pathKey);

        return is_string($path) && $path !== '' && Storage::disk('public')->exists($path);
    }

    private function deleteStoredFile(mixed $path): void
    {
        if (! is_string($path) || $path === '') {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
