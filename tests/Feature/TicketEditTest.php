<?php

namespace Tests\Feature;

use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\MTicketTag;
use App\Enums\TicketStatusEnum;
use App\Enums\CategoryEnum;

class TicketEditTest extends TestCase
{
    use RefreshDatabase;

    /**
     *　正しくチケットを編集できるか
     **/
    public function test_edit_ticket(): void
    {
        $user = User::firstOrFail();
        $this->actingAs($user);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'content' => "ticket edit test before",
            'title' => "Test Ticket Title",
            'status_no' => TicketStatusEnum::Draft->value,
            'is_recruitment' => true,
            'tags' => ['PHP', 'Laravel'],
        ]);

        // 編集用データ
        $test_data = [
            'id' => $ticket->id,
            'user_id' => $user->id,
            'category_id' => CategoryEnum::SeminarAndStudy->value,
            'content' => "ticket edit test after",
            'title' => "Updated Ticket Title",
            'status_no' => TicketStatusEnum::Published->value,
            'is_recruitment' => false,
            'tags' => ['JavaScript']
        ];

        $response = $this->put('/api/tickets/' . $ticket->id, $test_data);
        $response->assertStatus(200)
            ->assertJson([
                'id' => $ticket->id,
                'user_id' => $ticket->user_id,
                'category_id' => CategoryEnum::SeminarAndStudy->value,
                'content' => "ticket edit test after",
                'title' => "Updated Ticket Title",
                'is_recruitment' => false,
                'status_no' => TicketStatusEnum::Published->value
            ])
            ->assertJsonPath('tags.0.label', 'JavaScript');

        // データベース検証
        $this->assertDatabaseHas('ticket', [
            'id' => $ticket->id,
            'user_id' => $user->id,
            'category_id' => CategoryEnum::SeminarAndStudy->value,
            'content' => "ticket edit test after",
            'title' => "Updated Ticket Title",
            'is_recruitment' => false,
            'status_no' => TicketStatusEnum::Published->value,
        ]);

        // ticket_tag 中間テーブルの検証
        $tagId = MTicketTag::where('label', 'JavaScript')->first()->id;
        $this->assertDatabaseHas('ticket_tag', [
            'ticket_id' => $ticket->id,
            'tag_id' => $tagId,
        ]);
    }

    /**
     * 異なるユーザーのチケットを編集しようとするとエラーが発生する
     */
    public function test_edit_ticket_other_user(): void
    {
        $user = User::firstOrFail();
        $this->actingAs($user);

        // 異なるユーザーを作成し、そのユーザーによってチケットを作成する
        $otherUser = User::factory()->create();
        $ticket = Ticket::create([
            'user_id' => $otherUser->id,
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'content' => "ticket edit test before",
            'title' => "Test Title",
            'status_no' => TicketStatusEnum::Draft->value,
            'tags' => ['PHP', 'Laravel']
        ]);

        // テストユーザーが異なるユーザーのチケットを編集しようとする
        $test_data = [
            'id' => $ticket->id,
            'user_id' => $otherUser->id,
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'content' => substr(bin2hex(random_bytes(16383)), 0, 16383),
            'title' => "Test Title",
            'status_no' => TicketStatusEnum::Draft->value,
            'tags' => ['PHP', 'Laravel']
        ];
        $response = $this->put('/api/tickets/' . $ticket->id, $test_data);

        // 404エラーとエラーメッセージ
        $response
            ->assertStatus(404)
            ->assertJson(['message' => "ticket not found"]);
    }

    /**
     * 文字数オーバー
     */
    public function test_edit_ticket_text_over_max(): void
    {
        // テストユーザーを作成
        $user = User::firstOrFail();
        $this->actingAs($user);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'content' => "ticket edit test before",
            'title' => "Test Title",
            'status_no' => TicketStatusEnum::Draft->value,
            'tags' => ['PHP', 'Laravel']
        ]);

        $test_data = [
            'id' => $ticket->id,
            'user_id' => $user->id,
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'content' => substr(bin2hex(random_bytes(16384)), 0, 16384),
            'title' => "Test Title",
            'status_no' => TicketStatusEnum::Draft->value,
            'tags' => ['PHP', 'Laravel']
        ];
        $response = $this->put('/api/tickets/' . $ticket->id, $test_data);
        $response
            ->assertStatus(400)
            ->assertJson(['message' => "The content field must not be greater than 16383 characters."]);
    }

    /**
     * 無効なStatusでチケット編集すると400エラーを返す
     */
    public function test_edit_ticket_invalid_status(): void
    {
        // テストユーザーを作成
        $user = User::firstOrFail();
        $this->actingAs($user);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'content' => "ticket edit test before",
            'title' => "Test Ticket Title",
            'status_no' => TicketStatusEnum::Draft->value,
            'tags' => ['PHP', 'Laravel']
        ]);

        // 無効なstatus値を含むテストデータを作成
        $invalidStatusNo = 50; // 無効なステータス値
        $test_data = [
            'id' => $ticket->id,
            'user_id' => $user->id,
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'content' => "ticket edit test after",
            'title' => "Updated Ticket Title",
            'status_no' => $invalidStatusNo,
            'tags' => ['PHP', 'Laravel']
        ];

        $response = $this->put('/api/tickets/' . $ticket->id, $test_data);

        $response
            ->assertStatus(400)
            ->assertJsonValidationErrors(['status_no']);
    }
}
