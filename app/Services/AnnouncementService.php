<?php

namespace App\Services;

use App\Models\User;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;

class AnnouncementService
{
    public function __construct(
        private PlatformSettingsService $settings,
    ) {}

    /** @return array<string, mixed>|null */
    public function forRequest(Request $request): ?array
    {
        if (! $this->settings->getBool('announcement_enabled')) {
            return null;
        }

        $message = RichTextSanitizer::sanitize((string) $this->settings->get('announcement_message', ''));

        if (RichTextSanitizer::isEmpty($message)) {
            return null;
        }

        $audience = (string) $this->settings->get('announcement_audience', 'authenticated');

        if (! $this->matchesAudience($audience, $request->user())) {
            return null;
        }

        $linkUrl = $this->settings->get('announcement_link_url');
        $linkLabel = trim((string) $this->settings->get('announcement_link_label', ''));

        return [
            'id' => md5($message.'|'.$audience),
            'message' => $message,
            'type' => $this->settings->get('announcement_type', 'info'),
            'linkUrl' => is_string($linkUrl) && $linkUrl !== '' ? $linkUrl : null,
            'linkLabel' => $linkLabel !== '' ? $linkLabel : 'Learn more',
            'dismissible' => $this->settings->getBool('announcement_dismissible', true),
        ];
    }

    private function matchesAudience(string $audience, ?User $user): bool
    {
        return match ($audience) {
            'all' => true,
            'authenticated' => $user !== null,
            'users' => $user !== null && ! $user->isAdmin(),
            'admins' => $user !== null && $user->isAdmin(),
            default => false,
        };
    }
}
