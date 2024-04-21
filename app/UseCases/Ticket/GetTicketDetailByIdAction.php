<?php

declare(strict_types=1);

namespace App\UseCases\Ticket;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class GetTicketDetailByIdAction
{
    public function __invoke($ticketId): Ticket
    {
        $ticket = Ticket::with([
            'answers' => function ($query) {
                $query->with('user:id,nickname'); // answersに関連するuserも読み込む
            },
            'user' => function ($query) {
                $query->select('id', 'nickname');
            }
        ])->find($ticketId);

        if (is_null($ticket)) {
            throw new ModelNotFoundException();
        }

        return $ticket;
    }
}
