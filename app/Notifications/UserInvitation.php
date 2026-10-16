<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInvitation extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $resetUrl,
        private readonly string $roleName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('biik.invitation_subject'))
            ->greeting(__('biik.invitation_greeting', ['name' => $notifiable->name]))
            ->line(__('biik.invitation_role', ['role' => $this->roleName]))
            ->line(__('biik.invitation_setup'))
            ->action(__('biik.invitation_set_password'), $this->resetUrl)
            ->line(__('biik.invitation_verify'))
            ->action(__('biik.invitation_login'), route('login'))
            ->salutation(__('biik.invitation_signoff'));
    }
}
