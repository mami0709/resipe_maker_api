<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmailNotification extends Notification
{
    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:4000');
        $verificationUrl = "{$frontendUrl}/registrationSuccess?token={$this->token}";

        return (new MailMessage)
            ->subject('【Tasukeai】ユーザー本登録のご案内')
            ->view(
                'emails.verify_email',
                ['url' => $verificationUrl]
            );
    }
}
