<?php

namespace App\Mail;

use App\Models\User;
use App\Services\BrandingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeactivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        $appName = app(BrandingService::class)->displayName();

        return new Envelope(
            subject: "Your {$appName} account has been deactivated",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.account-deactivated',
            with: [
                'appName' => app(BrandingService::class)->displayName(),
            ],
        );
    }
}
