<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sends a 6-digit password reset code to a buyer.
 * Mirrors the pattern of EmailVerificationNotification.
 * The code itself is never logged or returned in API responses;
 * only the mail driver writes it (to storage/logs/laravel.log when MAIL_MAILER=log).
 */
class PasswordResetNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset Your Password - mimoo')
            ->greeting('Hi there!')
            ->line('We received a request to reset your mimoo account password.')
            ->line('Your password reset code is:')
            ->line('**' . $this->code . '**')
            ->line('This code will expire in 5 minutes.')
            ->line('If you did not request a password reset, please ignore this email — your account is safe.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
