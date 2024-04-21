<?php

declare(strict_types=1);

namespace App\UseCases\Ticket;

use App\Models\User;
use App\Models\Ticket;
use App\Models\MTicketTag;
use Illuminate\Support\Facades\DB;
use App\Enums\TicketStatusEnum;
use App\Notifications\TicketCreatedNotification;

final class TicketCreateAction
{
    public function __invoke(User $user, array $requestData): Ticket
    {
        return DB::transaction(function () use ($user, $requestData) {
            $ticket = new Ticket();
            $ticket->user_id = $user->id;
            $ticket->status_no = $requestData['status_no'] ?? TicketStatusEnum::Draft->value;
            $ticket->title = $requestData['title'];
            $ticket->content = $requestData['content'];
            $ticket->category_id = $requestData['category_id'];
            $ticket->is_recruitment = true;
            $ticket->save();

            // Slack通知のトリガー
            $ticket->user->notify(new TicketCreatedNotification($ticket));

            // タグの処理
            if (!empty($requestData['tags'])) {
                $tagIds = MTicketTag::whereIn('label', $requestData['tags'])->pluck('id');
                $ticket->tags()->sync($tagIds);
            }

            return $ticket->load('tags');
        });
    }
}
