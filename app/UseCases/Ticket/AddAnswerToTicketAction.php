<?php

declare(strict_types=1);

namespace App\UseCases\Ticket;

use App\Models\Ticket;
use App\Models\TicketAnswer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Notifications\TicketAnsweredNotification;

final class AddAnswerToTicketAction
{
    public function __invoke(int $ticket_id, int $user_id, string $content): TicketAnswer
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);

            if ($ticket->answers->isEmpty()) {
                $ticket->is_recruitment = true;
                $ticket->save();
            }
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('Ticket not found', 0, $e);
        }
        $answer = TicketAnswer::create([
            'ticket_id' => $ticket_id,
            'user_id' => $user_id,
            'content' => $content,
        ]);

        // チケット作成者が回答者でない場合のみ通知を送信
        if ($ticket->user_id !== $user_id) {
            $ticket->user->notify(new TicketAnsweredNotification($ticket));
        }

        return $answer;
    }
}
