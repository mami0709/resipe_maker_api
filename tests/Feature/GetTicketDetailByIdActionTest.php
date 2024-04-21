<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TicketAnswer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetTicketDetailByIdActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正しい形式でチケット詳細を取得できるか
     */
    public function test_invoke_with_valid_ticket_id(): void
    {
        // テストユーザーを作成
        $user = User::firstOrFail();
        $ticket = Ticket::firstOrFail();

        $this->actingAs($user);

        // HTTP リクエストを行い、得られたレスポンスの構造を確認
        $response = $this->get('/api/tickets/' . $ticket->id . '/detail');
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'category_id',
                'content',
                'title',
                'user_id',
                'status_no',
                'is_recruitment',
                'created_at',
                'updated_at',
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
            ]);
    }

    /**
     * チケットIDが存在しない場合は404
     */
    public function test_invoke_with_invalid_ticket_id(): void
    {
        // テストユーザーを作成
        $user = User::firstOrFail();
        $this->actingAs($user);

        $response = $this->get('/api/tickets/9999/detail');
        $response
            ->assertStatus(404)
            ->assertJson([
                'error' => true,
                'message' => 'Ticket not found'
            ]);
    }

    /**
     * addAnswerしたときanswersの中に正しいレスポンスを返すか
     */
    public function test_invoke_with_answer_attached(): void
    {
        // テストユーザーとチケットを作成
        $user = User::firstOrFail();
        $ticket = Ticket::firstOrFail();

        $answer = TicketAnswer::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'content' => 'これはテスト回答です'
        ]);

        $this->actingAs($user);

        $response = $this->get('/api/tickets/' . $ticket->id . '/detail');
        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $ticket->id,
                'answers' => [
                    [
                        'id' => $answer->id,
                        'ticket_id' => $ticket->id,
                        'user_id' => $user->id,
                        'content' => $answer->content,
                    ]
                ]
            ]);
    }
}
