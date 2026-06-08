<?php

namespace App\Services;

use App\Models\User;

class SiteLimitService
{
    public function __construct(
        private PlatformSettingsService $settings,
    ) {}

    public function isUnlimited(User $user): bool
    {
        return $user->isAdmin();
    }

    public function maxFor(User $user): int
    {
        return $this->settings->getInt('max_sites_per_user', 2);
    }

    /** @return int|null Null when the user has no site cap (admins). */
    public function maxForDisplay(User $user): ?int
    {
        return $this->isUnlimited($user) ? null : $this->maxFor($user);
    }

    public function used(User $user): int
    {
        return $user->sites()->count();
    }

    public function remaining(User $user): int
    {
        if ($this->isUnlimited($user)) {
            return PHP_INT_MAX;
        }

        return max(0, $this->maxFor($user) - $this->used($user));
    }

    public function canCreate(User $user): bool
    {
        if ($this->isUnlimited($user)) {
            return true;
        }

        return $this->remaining($user) > 0;
    }
}
