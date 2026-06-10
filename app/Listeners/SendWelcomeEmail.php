<?php

namespace App\Listeners;

use App\Services\TransactionalEmailService;
use Illuminate\Auth\Events\Registered;

class SendWelcomeEmail
{
    public function __construct(
        private TransactionalEmailService $transactionalEmail,
    ) {}

    public function handle(Registered $event): void
    {
        $this->transactionalEmail->sendWelcome($event->user);
    }
}
