<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\TicketStatusEnum;
use App\Enums\CategoryEnum;

class GetAllTicketsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正しい形式でチケット一覧を取得できるか
     */
    public function test_invoke_returns_all_tickets(): void
    {
        // テストケースに合わせたチケットを作成する為、Seederで作成したチケットを全て削除
        Ticket::query()->delete();
        // 既存のユーザーを取得
        $user = User::firstOrFail();
        $this->actingAs($user);
        Ticket::factory()->count(3)->state(['status_no' => TicketStatusEnum::Published->value])->create();
        Ticket::factory()->count(2)->state(['status_no' => TicketStatusEnum::Draft->value])->create();

        $response = $this->get('/api/tickets/all');

        $response->assertStatus(200);

        $responseData = $response->decodeResponseJson();
        $data = $responseData['data'];

        // status_no が 20 のチケットの数を確認
        $this->assertCount(3, $data);

        // 各Ticketの詳細を確認
        foreach ($data as $ticketData) {
            $this->assertIsInt($ticketData['id']);
            $this->assertIsInt($ticketData['category_id']);
            $this->assertIsString($ticketData['content']);
            $this->assertIsString($ticketData['title']);
            $this->assertIsInt($ticketData['user_id']);
            $this->assertEquals(TicketStatusEnum::Published->value, $ticketData['status_no']);
            $this->assertIsBool($ticketData['is_recruitment']);
            $this->assertIsString($ticketData['created_at']);
            $this->assertIsString($ticketData['updated_at']);
        }
    }

    /**
     * 特定のカテゴリを指定してチケットを取得
     */
    public function test_invoke_with_category_filter(): void
    {
        // テストケースに合わせたチケットを作成する為、Seederで作成したチケットを全て削除
        Ticket::query()->delete();

        $user = User::firstOrFail();
        $this->actingAs($user);

        // カテゴリ1とカテゴリ2のチケットを作成
        Ticket::factory()->count(2)->create([
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'status_no' => TicketStatusEnum::Published->value
        ]);
        Ticket::factory()->count(1)->create([
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'status_no' => TicketStatusEnum::Draft->value
        ]);
        Ticket::factory()->count(2)->create([
            'category_id' => CategoryEnum::SeminarAndStudy->value,
            'status_no' => TicketStatusEnum::Published->value
        ]);

        // テストするページとページあたりのアイテム数
        $perPage = 5;
        $page = 1;

        // カテゴリ1のチケットのみを取得
        $category = CategoryEnum::BugAndConsultation->value;
        $response = $this->get("/api/tickets/all/{$category}?page={$page}&per_page={$perPage}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'category_id',
                        'content',
                        'title',
                        'created_at',
                        'updated_at'
                    ],
                ],
            ]);
        $responseData = $response->decodeResponseJson();

        // カテゴリ1かつ status_no が 20 のチケットの数を確認
        $categoryOneTickets = collect($responseData['data'])->where('category_id', $category);
        $this->assertCount(2, $categoryOneTickets);
        foreach ($categoryOneTickets as $ticket) {
            $this->assertEquals(TicketStatusEnum::Published->value, $ticket['status_no']);
        }

        // ページネーションのメタデータを検証
        $this->assertEquals($page, $responseData['current_page']);
        $this->assertEquals($perPage, $responseData['per_page']);
    }

    /**
     * 空の場合は空配列を返却
     */
    public function test_invoke_with_no_tickets_in_database(): void
    {
        // テストケースに合わせたチケットを作成する為、Seederで作成したチケットを全て削除
        Ticket::query()->delete();

        $user = User::firstOrFail();
        $this->actingAs($user);

        $response = $this->get('/api/tickets/all');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data',
            ]);

        $responseData = $response->decodeResponseJson();

        // dataが空であることを確認
        $this->assertEmpty($responseData['data']);

        // ページネーションのメタデータが存在するが、totalが0であること確認
        $this->assertEquals(0, $responseData['total']);
    }

    /**
     * カテゴリデータが正しく取得できるかのテスト
     */
    public function test_categories_are_correctly_fetched(): void
    {
        // テストケースに合わせたチケットを作成する為、Seederで作成したチケットを全て削除
        Ticket::query()->delete();

        $user = User::firstOrFail();
        $this->actingAs($user);

        Ticket::factory()->count(3)->create([
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'status_no' => TicketStatusEnum::Published->value
        ]);
        Ticket::factory()->count(2)->create([
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'status_no' => TicketStatusEnum::Draft->value
        ]);

        $response = $this->get('/api/tickets/all');

        $response->assertStatus(200);

        $responseData = $response->decodeResponseJson();
        $data = $responseData['data'];

        // category_id が BugAndConsultation かつ status_no が Published のチケットの数を確認
        $categoryOneTickets = collect($data)->where('category_id', CategoryEnum::BugAndConsultation->value);
        $this->assertCount(3, $categoryOneTickets);
        foreach ($categoryOneTickets as $ticket) {
            $this->assertEquals(TicketStatusEnum::Published->value, $ticket['status_no']);
        }
    }

    /**
     * パスパラメータでカテゴリーIDを指定して取得できるかのテスト
     */
    public function test_invoke_with_category_parameter(): void
    {
        // テストケースに合わせたチケットを作成する為、Seederで作成したチケットを全て削除
        Ticket::query()->delete();

        $user = User::firstOrFail();
        $this->actingAs($user);

        Ticket::factory()->count(2)->create([
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'status_no' => TicketStatusEnum::Published->value
        ]);
        Ticket::factory()->count(1)->create([
            'category_id' => CategoryEnum::BugAndConsultation->value,
            'status_no' => TicketStatusEnum::Draft->value
        ]);
        Ticket::factory()->count(2)->create([
            'category_id' => CategoryEnum::SeminarAndStudy->value,
            'status_no' => TicketStatusEnum::Published->value
        ]);

        // カテゴリを指定してリクエストを送信
        $categoryOne = CategoryEnum::BugAndConsultation->value;
        $response = $this->get("/api/tickets/all/{$categoryOne}");

        $response->assertStatus(200);
        $responseData = $response->decodeResponseJson();

        // カテゴリID 1 かつ statusが Published のチケットの数を検証
        $categoryOneTickets = collect($responseData['data'])->where('category_id', $categoryOne);
        $this->assertCount(2, $categoryOneTickets);
        foreach ($categoryOneTickets as $ticket) {
            $this->assertEquals(TicketStatusEnum::Published->value, $ticket['status_no']);
        }

        // カテゴリID 2をパスパラメータとして指定してリクエストを送信
        $categoryTwo = CategoryEnum::SeminarAndStudy->value;
        $response = $this->get("/api/tickets/all/{$categoryTwo}");

        $response->assertStatus(200);
        $responseData = $response->decodeResponseJson();

        // カテゴリID 2 かつ status が Published のチケットの数を検証
        $categoryTwoTickets = collect($responseData['data'])->where('category_id', $categoryTwo);
        $this->assertCount(2, $categoryTwoTickets);
        foreach ($categoryTwoTickets as $ticket) {
            $this->assertEquals(TicketStatusEnum::Published->value, $ticket['status_no']);
        }
    }

    /**
     *　ページネーションが正しく適用されていて正しいデータ構造か
     */
    public function test_pagination_is_applied_correctly(): void
    {
        // テストケースに合わせたチケットを作成する為、Seederで作成したチケットを全て削除
        Ticket::query()->delete();

        $user = User::firstOrFail();
        $this->actingAs($user);

        Ticket::factory()->count(30)->create(['status_no' => TicketStatusEnum::Published->value]);
        Ticket::factory()->count(20)->create(['status_no' => TicketStatusEnum::Draft->value]);

        $perPage = 5;
        $page = 2;

        $response = $this->get("/api/tickets/all?page={$page}&per_page={$perPage}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'category_id',
                        'content',
                        'title',
                        'created_at',
                        'updated_at'
                    ],
                ],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links' => [
                    '*' => [
                        'url',
                        'label',
                        'active'
                    ],
                ],
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ]);

        $responseData = $response->decodeResponseJson();

        // ページネーションのメタデータを検証
        $this->assertEquals($page, $responseData['current_page']);
        $this->assertEquals($perPage, count($responseData['data']));
        $this->assertEquals($perPage, $responseData['per_page']);
        $this->assertEquals(30, $responseData['total']);

        foreach ($responseData['data'] as $ticketData) {
            // status が Published のチケットのみが含まれているかを確認
            $this->assertEquals(TicketStatusEnum::Published->value, $ticketData['status_no']);
        }

        // リンクが正しく生成されているか
        $this->assertNotNull($responseData['first_page_url']);
        $this->assertNotNull($responseData['last_page_url']);
        $this->assertNotNull($responseData['next_page_url']);
        // 前のページがある場合、prev_page_urlはnullでないこと確認
        if ($responseData['current_page'] > 1) {
            $this->assertNotNull($responseData['prev_page_url']);
        }
    }
}
