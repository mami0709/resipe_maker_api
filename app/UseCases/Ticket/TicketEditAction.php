<?php

declare(strict_types=1);

namespace App\UseCases\Ticket;

use App\Models\Ticket;
use App\Models\User;
use App\Models\MTicketTag;

final class TicketEditAction
{
    public function __invoke(int $ticket_id, Ticket $update_data, User $user): Ticket
    {
        $ticket = Ticket::findOrFail($ticket_id);

        if ($ticket->user_id !== $user->id) {
            abort(404, 'ticket not found');
        }

        $ticket->fill($update_data->toArray());
        $ticket->save();

        // タグの処理
        if (isset($update_data->tags)) {
            $tagIds = MTicketTag::whereIn('label', $update_data->tags)->pluck('id');
            $ticket->tags()->sync($tagIds);
        }

        return $ticket->load('tags');
    }
}
