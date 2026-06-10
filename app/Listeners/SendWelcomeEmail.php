<?php

namespace App\Listeners;

use App\Services\TransactionalEmailService;
use Illuminate\Auth\Events\Verified;

class SendWelcomeEmail
{
    public function __construct(
        private TransactionalEmailService $transactionalEmail,
    ) {}

    public function handle(Verified $event): void
    {
        $this->transactionalEmail->sendWelcome($event->user);
    }
}
