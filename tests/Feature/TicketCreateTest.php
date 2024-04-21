<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\TicketStatusEnum;
use App\Enums\CategoryEnum;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TicketCreatedNotification;

class TicketCreateTest extends TestCase
{
    use RefreshDatabase;

    /**
     *　正しくチケット作成できるか
     */
    public function test_create_ticket(): void
    {
        $user = User::firstOrFail();
        $this->actingAs($user);

        // 作成するチケットのデータ
        $testData = [
            'user_id' => $user->id,
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'title' => 'Test Ticket Title',
            'content' => 'This is a test content for the ticket.',
            'status_no' => TicketStatusEnum::Draft->value,
            'tags' => ['PHP', 'Laravel']
        ];

        $response = $this->post('/api/tickets/create', $testData);
        $response->assertStatus(201);

        // データベースでチケットの存在を確認
        $response->assertJsonFragment([
            'user_id' => $testData['user_id'],
            'category_id' => $testData['category_id'],
            'title' => $testData['title'],
            'content' => $testData['content'],
            'status_no' => $testData['status_no'],
            'is_recruitment' => true,
        ]);

        // タグを検証
        $tags = $response->json('tags');
        $this->assertEquals(['PHP', 'Laravel'], array_column($tags, 'label'));
    }

    /**
     *　文字数オーバー
     */
    public function test_create_ticket_text_over_max(): void
    {
        $user = User::firstOrFail();
        $this->actingAs($user);

        $testData = [
            'user_id' => $user->id,
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'title' => 'Test Ticket Title',
            'content' => str_repeat('a', 16384),
            'status_no' => TicketStatusEnum::Draft->value,
            'tags' => ['PHP', 'Laravel']
        ];

        $response = $this->post('/api/tickets/create', $testData);
        $response->assertStatus(400)->assertJson(['message' => "The content field must not be greater than 16383 characters."]);
    }

    /**
     *　無効なStatusでチケット作成すると400エラーを返す
     */
    public function test_create_ticket_with_invalid_status(): void
    {
        $user = User::firstOrFail();
        $this->actingAs($user);

        $categoryIds = array_map(fn ($cat) => $cat->value, CategoryEnum::cases());
        $randomCategoryId = $categoryIds[array_rand($categoryIds)];

        $testData = [
            'user_id' => $user->id,
            'category_id' => $randomCategoryId,
            'title' => 'Test Ticket Title',
            'content' => 'This is a test content for the ticket.',
            'status_no' => 999, // 故意に無効なステータス番号を設定
        ];

        $response = $this->post('/api/tickets/create', $testData);
        $response->assertStatus(400)->assertJsonValidationErrors(['status_no']);
    }


    /**
     * 特定のカテゴリIDでチケットが作成できるかをテスト
     */
    public function test_create_ticket_with_specific_category(): void
    {
        $user = User::firstOrFail();
        $this->actingAs($user);

        $category_id = CategoryEnum::BugAndConsultation->value;
        $testData = [
            'user_id' => $user->id,
            'category_id' => $category_id,
            'title' => 'Test Ticket with Specific Category',
            'content' => 'Content for the specific category test.',
            'status_no' => TicketStatusEnum::Draft->value,
        ];

        $response = $this->post('/api/tickets/create', $testData);
        $response->assertStatus(201)->assertJson($testData);

        // データベースにカテゴリIDを持つチケットが存在するかを検証
        $this->assertDatabaseHas('ticket', [
            'user_id' => $testData['user_id'],
            'category_id' => $testData['category_id'],
            'title' => $testData['title'],
            'content' => $testData['content'],
            'status_no' => $testData['status_no'],
        ]);
    }

    /**
     * 無効な category_id を使用してチケットを作成すると500エラー
     */
    public function test_create_ticket_with_invalid_category(): void
    {
        $user = User::firstOrFail();
        $this->actingAs($user);

        // 存在しない category_id を定義
        $invalid_category_id = 999;
        $testData = [
            'user_id' => $user->id,
            'category_id' => $invalid_category_id,
            'title' => 'Test Ticket with Invalid Category',
            'content' => 'Content for the invalid category test.',
            'status_no' => TicketStatusEnum::Draft->value,
        ];

        $response = $this->post('/api/tickets/create', $testData);
        $response->assertStatus(400);
    }

    /**
     * resolution_state_no未指定でチケット作成しても、正しくresolution_state_noが10(未回答)の状態で作成されるかテスト
     */
    public function test_create_ticket_without_specifying_resolution_state_no(): void
    {
        $user = User::firstOrFail();
        $this->actingAs($user);

        // Enumを使用してテストデータを設定
        $testData = [
            'user_id' => $user->id,
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'title' => 'Test Ticket Without Resolution State No',
            'content' => 'This is a test content for the ticket.',
            'status_no' => TicketStatusEnum::Draft->value,
        ];

        $response = $this->post('/api/tickets/create', $testData);
        $response->assertStatus(201);

        $ticketData = $testData;
        $ticketData['is_recruitment'] = true;

        $response->assertJsonFragment($ticketData);

        // データベースでチケットの存在とresolution_state_noを確認
        $this->assertDatabaseHas('ticket', [
            'user_id' => $testData['user_id'],
            'category_id' => $testData['category_id'],
            'title' => $testData['title'],
            'content' => $testData['content'],
            'status_no' => $testData['status_no'],
            'is_recruitment' => true,
        ]);
    }

    /**
     * チケット作成時にSlack通知が正常に行われるかテスト
     */
    public function test_ticket_creation_sends_slack_notification(): void
    {
        // このテストケースのみ本番環境として扱う(本番環境でのみSlack通知が行われる為)
        $this->app->detectEnvironment(function () {
            return 'production';
        });

        Notification::fake();

        $user = User::firstOrFail();
        $this->actingAs($user);

        $testData = [
            'user_id' => $user->id,
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'title' => 'Test Ticket Title',
            'content' => 'This is a test content for the ticket.',
            'status_no' => TicketStatusEnum::Draft->value,
            'tags' => ['PHP', 'Laravel']
        ];

        $this->post('/api/tickets/create', $testData);

        // 指定した通知が指定したユーザーに送信されたことをアサート
        Notification::assertSentTo(
            [$user],
            TicketCreatedNotification::class
        );
    }

    /**
     * チケット作成が失敗した場合、Slack通知が行われないことをテスト
     */
    public function test_no_slack_notification_on_ticket_creation_failure(): void
    {
        Notification::fake();

        $user = User::firstOrFail();
        $this->actingAs($user);

        $invalidTestData = [
            // 不正なテストデータ
            'user_id' => $user->id,
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'title' => 'Test Ticket Title',
            'content' => 'This is a test content for the ticket.',
            'status_no' => 999, // 故意に無効なステータス番号を設定
        ];

        $response = $this->post('/api/tickets/create', $invalidTestData);
        $response->assertStatus(400);

        // Slack通知が送信されなかったことを確認
        Notification::assertNotSentTo(
            [$user],
            TicketCreatedNotification::class
        );
    }
}
