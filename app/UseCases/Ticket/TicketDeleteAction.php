<?php

declare(strict_types=1);

namespace App\UseCases\Ticket;

use App\Http\Requests\TicketDeleteRequest;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\ItemNotFoundException;

final class TicketDeleteAction
{
    public function __invoke($ticket_id, $user_id): bool
    {
        $ticket = Ticket::where([
                'id' => $ticket_id,
                'user_id' => $user_id
            ])->first();

        if(is_null($ticket)) {
            abort('ticket not found');
        }

        return $ticket->delete();
    }
}
