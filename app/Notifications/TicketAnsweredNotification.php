<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Ticket;

class TicketAnsweredNotification extends Notification
{
    protected $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $frontendUrl = env('FRONTEND_URL');
        $url = $frontendUrl . '/articleDetail/?ticketId=' . $this->ticket->id;

        return (new MailMessage)
            ->subject('【Tasukeai】チケットに新しい回答があります')
            ->view(
                'emails.ticket_answered',
                ['url' => $url]
            );
    }
}
