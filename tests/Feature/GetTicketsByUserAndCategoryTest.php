<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\TicketStatusEnum;
use App\Enums\CategoryEnum;

class GetTicketsByUserAndCategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ユーザーIDとカテゴリを指定してチケットを取得し、正しいレスポンスか確認
     */
    public function test_fetch_tickets_by_user_id_and_category(): void
    {
        // テストケースに合わせたチケットを作成する為、Seederで作成したチケットを全て削除
        Ticket::query()->delete();

        // テストユーザーを作成
        $user = User::firstOrFail();
        $this->actingAs($user);

        // テストユーザーのチケットを作成
        Ticket::factory()->count(5)->create([
            'user_id' => $user->id,
            'category_id' => CategoryEnum::BugAndConsultation->value
        ]);

        // ユーザーIDとカテゴリを指定してチケットを取得
        $categoryId = CategoryEnum::BugAndConsultation->value;
        $response = $this->get('/api/tickets/' . $user->id . '/' . $categoryId);
        $responseData = $response->decodeResponseJson();

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'category_id',
                        'content',
                        'title',
                        'user_id',
                        'created_at',
                        'updated_at',
                        'status_no',
                        'is_recruitment'
                    ]
                ]
            ]);

        $this->assertCount(5, $responseData['data']);

        // 各チケットの詳細を確認
        foreach ($responseData['data'] as $ticketData) {
            $this->assertIsInt($ticketData['id']);
            $this->assertEquals($categoryId, $ticketData['category_id']);
            $this->assertEquals($user->id, $ticketData['user_id']);
            $this->assertIsString($ticketData['content']);
            $this->assertIsString($ticketData['title']);
            $this->assertIsInt($ticketData['status_no']);
            $this->assertIsBool($ticketData['is_recruitment']);
            $this->assertIsString($ticketData['created_at']);
            $this->assertIsString($ticketData['updated_at']);
        }
    }


    /**
     * ユーザーIDのみを指定してチケットを取得し、正しいレスポンスか
     */
    public function test_fetch_tickets_by_user_id_only(): void
    {
        // テストケースに合わせたチケットを作成する為、Seederで作成したチケットを全て削除
        Ticket::query()->delete();

        // テストユーザーを作成
        $user = User::firstOrFail();
        $this->actingAs($user);

        $tickets = Ticket::factory()->count(5)->create([
            'user_id' => $user->id,
            'status_no' => TicketStatusEnum::Published->value
        ]);

        // ユーザーIDのみを指定してチケットを取得
        $response = $this->get('/api/tickets/' . $user->id);

        // レスポンスを検証
        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'category_id',
                        'content',
                        'title',
                        'user_id',
                        'created_at',
                        'updated_at',
                        'status_no',
                        'is_recruitment'
                    ],
                ],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ])
            ->assertJsonPath('data.0.status_no', TicketStatusEnum::Published->value)
            ->assertJsonPath('data.1.status_no', TicketStatusEnum::Published->value)
            ->assertJsonPath('data.2.status_no', TicketStatusEnum::Published->value)
            ->assertJsonPath('data.3.status_no', TicketStatusEnum::Published->value)
            ->assertJsonPath('data.4.status_no', TicketStatusEnum::Published->value);
    }


    /**
     * 存在しないUserIdの場合は404
     */
    public function test_invalid_user_id_returns_error(): void
    {
        // テストユーザーを作成
        $user = User::firstOrFail();
        $this->actingAs($user);

        $invalidUserId = 9999;
        $response = $this->get('/api/tickets/' . $invalidUserId);

        $response
            ->assertStatus(404)
            ->assertJson([
                'error' => true,
                'message' => 'User not found'
            ]);
    }

    /**
     * レスポンスが空の場合は空配列を返却
     */
    public function test_no_tickets_for_given_user_id(): void
    {
        // テストケースに合わせたチケットを作成する為、Seederで作成したチケットを全て削除
        Ticket::query()->delete();

        // テストユーザーを作成
        $user = User::firstOrFail();
        $this->actingAs($user);

        $response = $this->get('/api/tickets/' . $user->id);

        $responseData = $response->decodeResponseJson();

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => []
            ]);
        $this->assertEmpty($responseData['data']);
    }

    /*
     * ページネーションが正しく適用されているかテスト
     */
    public function test_pagination_is_applied_correctly(): void
    {
        // テストケースに合わせたチケットを作成する為、Seederで作成したチケットを全て削除
        Ticket::query()->delete();

        $user = User::firstOrFail();
        $this->actingAs($user);

        Ticket::factory()->count(50)->create(['user_id' => $user->id]);

        $perPage = 5;
        $page = 2;

        // ページネーションを含むエンドポイントをリクエスト
        $response = $this->get("/api/tickets/{$user->id}?page={$page}&per_page={$perPage}");

        $responseData = $response->decodeResponseJson();

        // ステータスコードとデータ構造を検証
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
                        'updated_at',
                        'status_no',
                        'is_recruitment'
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

        // ページネーションのメタデータを検証
        $this->assertEquals($page, $responseData['current_page']);
        $this->assertEquals($perPage, count($responseData['data']));
        $this->assertEquals($perPage, $responseData['per_page']);
        $this->assertEquals(50, $responseData['total']);

        // リンクが正しく生成されているか検証
        $this->assertNotNull($responseData['first_page_url']);
        $this->assertNotNull($responseData['last_page_url']);
        $this->assertNotNull($responseData['next_page_url']);
        // ページ2では前のページが存在するため、prev_page_urlはnullでないことを確認
        $this->assertNotNull($responseData['prev_page_url']);
        // ページ番号とページに含まれるアイテムの範囲が正しいか確認
        $this->assertEquals($page, $responseData['current_page']);
        $this->assertEquals($perPage * ($page - 1) + 1, $responseData['from']);
        $this->assertEquals($perPage * $page, $responseData['to']);
    }
}
