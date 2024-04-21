<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthCheckTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証されているユーザーに対して200ステータスとユーザーIDを返す
     */
    public function test_auth_check_with_authenticated_user(): void
    {
        $user = User::factory()->create();
        // ユーザーとしてログイン
        $this->actingAs($user);

        // エンドポイントのテスト
        $response = $this->get('/api/auth/check');
        $response->assertStatus(200);
        $response->assertJson(['userId' => $user->id]);
    }

    /**
     * 認証されていないユーザーに対して401ステータスとエラーメッセージを返す
     */
    public function test_auth_check_with_unauthenticated_user(): void
    {
        // エンドポイントのテスト
        $response = $this->get('/api/auth/check');
        $response->assertStatus(401);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    /**
     * メール認証が完了していないユーザーに対して403ステータスを返す
     */
    public function test_auth_check_with_unverified_user(): void
    {
        $unverifiedUser = User::factory()->unverified()->create();

        $this->actingAs($unverifiedUser);

        $response = $this->get('/api/auth/check');
        $response->assertStatus(403);
    }
}
