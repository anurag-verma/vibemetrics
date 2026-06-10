<?php

namespace App\Services;

use App\Mail\AccountDeactivatedMail;
use App\Mail\PasswordChangedMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class TransactionalEmailService
{
    public const TYPE_WELCOME = 'welcome';

    public const TYPE_PASSWORD_CHANGED = 'password_changed';

    public const TYPE_ACCOUNT_DEACTIVATED = 'account_deactivated';

    /** @var array<string, string> */
    private const TYPE_SETTING_KEYS = [
        self::TYPE_WELCOME => 'email_welcome_enabled',
        self::TYPE_PASSWORD_CHANGED => 'email_password_changed_enabled',
        self::TYPE_ACCOUNT_DEACTIVATED => 'email_account_deactivated_enabled',
    ];

    public function __construct(
        private PlatformSettingsService $settings,
    ) {}

    public function sendWelcome(User $user): void
    {
        $this->send(self::TYPE_WELCOME, $user, new WelcomeMail($user));
    }

    public function sendPasswordChanged(User $user): void
    {
        $this->send(self::TYPE_PASSWORD_CHANGED, $user, new PasswordChangedMail($user));
    }

    public function sendAccountDeactivated(User $user): void
    {
        $this->send(self::TYPE_ACCOUNT_DEACTIVATED, $user, new AccountDeactivatedMail($user));
    }

    public function isTypeEnabled(string $type): bool
    {
        if (! $this->settings->getBool('transactional_emails_enabled', true)) {
            return false;
        }

        $settingKey = self::TYPE_SETTING_KEYS[$type] ?? null;

        if ($settingKey === null) {
            return false;
        }

        return $this->settings->getBool($settingKey, true);
    }

    private function send(string $type, User $user, Mailable $mailable): void
    {
        if (! $this->isTypeEnabled($type)) {
            return;
        }

        Mail::to($user)->send($mailable);
    }
}
