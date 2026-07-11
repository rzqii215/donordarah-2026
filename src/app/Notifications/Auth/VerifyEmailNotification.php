<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends BaseVerifyEmail
{
    public function toMail(mixed $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl(
            $notifiable
        );

        $userName = filled($notifiable->name ?? null)
            ? (string) $notifiable->name
            : 'Pengguna';

        $expiresInMinutes = (int) config(
            'auth.verification.expire',
            60
        );

        return (new MailMessage)
            ->subject('Verifikasi Email Akun Donor Darah')
            ->view('emails.auth.verify-email', [
                'appName' => (string) config(
                    'app.name',
                    'Donor Darah'
                ),
                'userName' => $userName,
                'verificationUrl' => $verificationUrl,
                'expiresInMinutes' => $expiresInMinutes,
            ]);
    }
}