<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TicketAnswer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetTicketsAnswersWithMatchingUserIdTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正しい user_id を提供した場合に期待通りのレスポンスを受け取ることを確認
     */
    public function test_fetch_tickets_with_matching_user_id(): void
    {
        // テストユーザーを作成
        $user = User::firstOrFail();
        $this->actingAs($user);

        $ticket = Ticket::firstOrFail();
        $answer = TicketAnswer::factory()->create(['ticket_id' => $ticket->id, 'user_id' => $user->id]);

        // user_id を指定してチケットを取得
        $response = $this->get('/api/tickets/answers/' . $user->id);
        $response
            ->assertStatus(200)
            ->assertJsonCount(1)  // 1つのチケットがマッチすることを期待
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'category_id',
                    'content',
                    'title',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'status_no',
                    'is_recruitment',
                    'answers' => [
                        '*' => [
                            'id',
                            'ticket_id',
                            'user_id',
                            'content',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            ])
            ->assertJsonFragment(['user_id' => $user->id]);
    }

    // 無効な user_id を提供した場合にエラーを受け取ることを確認
    public function test_invalid_user_id_returns_error(): void
    {
        // テストユーザーを作成
        $user = User::firstOrFail();
        $this->actingAs($user);

        $invalidUserId = 9999;
        $response = $this->get('/api/tickets/answers/' . $invalidUserId);

        $response
            ->assertStatus(404)
            ->assertJson(['error' => 'User not found']);
    }


    // マッチするチケットがない場合に空の配列を受け取ることを確認
    public function test_no_matching_tickets_returns_empty_array(): void
    {
        // テストユーザーを作成
        $user = User::firstOrFail();
        $anotherUser = User::factory()->create();
        $this->actingAs($user);

        // anotherUser のチケットを作成
        $ticket = Ticket::firstOrFail();
        $answer = TicketAnswer::factory()->create(['ticket_id' => $ticket->id, 'user_id' => $anotherUser->id]);

        $response = $this->get('/api/tickets/answers/' . $user->id);

        $response
            ->assertStatus(200)
            ->assertJsonCount(0)
            ->assertJson([]);
    }
}
