<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Notifications\TicketAnsweredNotification;
use Illuminate\Support\Facades\Notification;

class TicketAnswerCreateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正しく回答を追加できるか
     */
    public function test_add_answer_to_ticket(): void
    {
        // テストユーザーとチケットを作成
        $user = User::firstOrFail();
        $ticket = Ticket::firstOrFail();

        $this->actingAs($user);

        $testData = [
            'user_id' => $user->id,
            'content' => 'これはテスト回答です',
        ];

        // チケットに回答を追加するリクエスト
        $response = $this->post('/api/tickets/' . $ticket->id . '/answers', $testData);
        $response
            ->assertStatus(201)
            ->assertJson([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'content' => $testData['content'],
            ]);

        // 正しく回答が保存されていることを確認するためにDBチェック
        $this->assertDatabaseHas('ticket_answer', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'content' => $testData['content'],
        ]);
    }

    /**
     * 存在しないチケットに回答を追加した時は404
     */
    public function test_add_answer_to_nonexistent_ticket(): void
    {
        // テストユーザーを作成
        $user = User::firstOrFail();
        $this->actingAs($user);

        $testData = [
            'user_id' => $user->id,
            'content' => 'これはテスト回答です',
        ];

        // 存在しないチケットに回答を追加するリクエストを送信
        $response = $this->post('/api/tickets/9999/answers', $testData);
        $response
            ->assertStatus(404)
            ->assertJson([
                'error' => true,
                'message' => 'Ticket not found'
            ]);
    }

    /**
     * 回答を追加したときにリレーションが正しいか確認
     */
    public function test_relationship_integrity(): void
    {
        // テストユーザーとチケットを作成
        $user = User::firstOrFail();
        $ticket = Ticket::firstOrFail();

        $this->actingAs($user);

        $testData = [
            'user_id' => $user->id,
            'content' => 'これはテスト回答です',
        ];

        // 回答を追加するリクエストを送信
        $response = $this->post('/api/tickets/' . $ticket->id . '/answers', $testData);
        $response
            ->assertStatus(201)
            ->assertJson($testData);

        //DBに回答が正しく保存されていることを確認
        $this->assertDatabaseHas('ticket_answer', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'content' => $testData['content'],
        ]);

        // リレーションシップの整合性を確認
        $answer = $ticket->answers()->first();
        $this->assertNotNull($answer);
        $this->assertEquals($user->id, $answer->user_id);
        $this->assertEquals($ticket->id, $answer->ticket_id);
    }

    /**
     * チケットに回答が追加されたときにメール通知が送信されるかテスト
     */
    public function test_send_notification_on_ticket_answer(): void
    {
        Notification::fake();

        // ユーザーとチケットを作成（ユーザーはチケット作成者とは異なるものとする）
        $user = User::factory()->create();
        $ticketOwner = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $ticketOwner->id]);

        $this->actingAs($user);

        $testData = [
            'user_id' => $user->id,
            'content' => 'これはテスト回答です'
        ];
        $this->post('/api/tickets/' . $ticket->id . '/answers', $testData);

        // チケットの所有者に対して通知が送信されたか確認
        Notification::assertSentTo(
            $ticketOwner,
            TicketAnsweredNotification::class
        );
    }
}
