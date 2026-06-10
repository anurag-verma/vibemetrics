<?php

namespace App\Mail;

use App\Models\User;
use App\Services\BrandingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        $appName = app(BrandingService::class)->displayName();

        return new Envelope(
            subject: "Your {$appName} password was changed",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.password-changed',
            with: [
                'appName' => app(BrandingService::class)->displayName(),
            ],
        );
    }
}
