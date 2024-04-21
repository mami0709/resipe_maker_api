<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TicketAnswer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketDeleteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正しくチケットと関連レコードを削除できるか
     */
    public function test_ticket_and_related_records_delete(): void
    {
        $user = User::firstOrFail();
        $ticket = Ticket::firstOrFail();

        $answer = TicketAnswer::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'content' => 'これはテスト回答です'
        ]);

        // チケットを削除
        $this->actingAs($user)->delete('/api/tickets/' . $ticket->id);

        // チケットと関連レコードの削除を確認
        $this->assertDatabaseMissing('ticket', ['id' => $ticket->id]);
        $this->assertDatabaseMissing('ticket_tag', ['ticket_id' => $ticket->id]);
        $this->assertDatabaseMissing('ticket_answer', ['id' => $answer->id]);
    }
}
