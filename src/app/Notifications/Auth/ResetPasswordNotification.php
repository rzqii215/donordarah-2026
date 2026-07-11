<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $email = (string) $notifiable->getEmailForPasswordReset();

        $passwordBroker = (string) config(
            'auth.defaults.passwords',
            'users'
        );

        $expiresInMinutes = (int) config(
            "auth.passwords.{$passwordBroker}.expire",
            60
        );

        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $email,
        ]);

        $userName = filled($notifiable->name ?? null)
            ? (string) $notifiable->name
            : 'Pengguna';

        return (new MailMessage)
            ->subject('Atur Ulang Password Akun Donor Darah')
            ->view('emails.auth.reset-password', [
                'appName' => (string) config('app.name', 'Donor Darah'),
                'userName' => $userName,
                'resetUrl' => $resetUrl,
                'expiresInMinutes' => $expiresInMinutes,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}