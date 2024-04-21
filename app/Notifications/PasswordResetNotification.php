<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetNotification extends Notification
{
    public $token;

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
        $resetUrl = url("/password/reset?token={$this->token}");

        return (new MailMessage)
            ->subject('【Tasukeai】パスワード再設定のご案内')
            ->view(
                'emails.password_reset',
                ['url' => $resetUrl]
            );
    }
}
