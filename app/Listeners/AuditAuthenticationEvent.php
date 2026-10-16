<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;

class AuditAuthenticationEvent
{
    public function handle(Login|Logout|Failed|Registered|PasswordReset|Verified $event): void
    {
        $eventName = match (true) {
            $event instanceof Login => 'authentication.login.succeeded',
            $event instanceof Logout => 'authentication.logout',
            $event instanceof Failed => 'authentication.login.failed',
            $event instanceof Registered => 'user.registered',
            $event instanceof PasswordReset => 'authentication.password.reset',
            default => 'user.email.verified',
        };

        $user = $event->user instanceof User ? $event->user : null;

        app(AuditLogger::class)->record(
            $eventName,
            $user,
            $event instanceof Login || $event instanceof Logout || $event instanceof Failed
                ? ['guard' => (string) $event->guard]
                : [],
            $event instanceof Failed ? null : $user,
        );
    }
}
