<?php

namespace App\Listeners;

use App\Services\TransactionalEmailService;
use Illuminate\Auth\Events\PasswordReset;

class SendPasswordChangedEmail
{
    public function __construct(
        private TransactionalEmailService $transactionalEmail,
    ) {}

    public function handle(PasswordReset $event): void
    {
        $this->transactionalEmail->sendPasswordChanged($event->user);
    }
}
