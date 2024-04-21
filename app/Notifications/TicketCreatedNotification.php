<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class TicketCreatedNotification extends Notification
{
    use Queueable;

    protected $ticket;

    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        // 本番環境のみSlack通知を行う
        if (app()->environment('production')) {
            return ['slack'];
        }

        // ローカル環境では通知を行わない
        return [];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack($notifiable)
    {
        $frontendUrl = env('FRONTEND_URL');
        $userNickname = $this->ticket->user->nickname;

        $title = mb_strimwidth($this->ticket->title, 0, 50, "...");
        $content = mb_strimwidth($this->ticket->content, 0, 100, "...");

        return (new SlackMessage)
            ->content('新しいチケットが作成されました！')
            ->attachment(function ($attachment) use ($notifiable, $frontendUrl, $userNickname, $title, $content) {
                $attachment->title('チケット詳細', $frontendUrl . '/articleDetail/?ticketId=' . $this->ticket->id)
                    ->fields([
                        'タイトル' => $title,
                        'ユーザー名' => $userNickname,
                        '内容' => $content
                    ]);
            });
    }
}
