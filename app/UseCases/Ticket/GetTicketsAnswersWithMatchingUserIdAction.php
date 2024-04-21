<?php

namespace App\UseCases\Ticket;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;

class GetTicketsAnswersWithMatchingUserIdAction
{
    public function __invoke(int $userId): Collection
    {
        $tickets = Ticket::whereHas('answers', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with('answers')->get();

        return $tickets;
    }
}
