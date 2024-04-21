<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /*
    　正常にログアウトできるか
    */
    public function test_logout(): void
    {
        $user = User::firstOrFail();
        // ユーザー認証
        $this->actingAs($user);

        $response = $this->post("/api/logout");
        // レスポンスが200(OK)であることを確認
        $response->assertStatus(200)->assertJson(['message' => 'Logged out successfully']);
    }

    /*
    　未認証ユーザーのログアウト試行
    */
    public function test_logout_unauthenticated_user(): void
    {
        $response = $this->post("/api/logout");
        $response->assertStatus(401); // 認証されていない場合は、401 Unauthorizedを期待
    }

    /*
    　ログアウト後、セッションが破棄されているか確認
    */
    public function test_access_denied_after_logout(): void
    {
        $user = User::firstOrFail();
        $this->actingAs($user, 'web');
        $this->post("/api/logout"); // ログアウト

        $response = $this->get("/api/user"); // 認証が必要なエンドポイントへのアクセス試行
        $response->assertStatus(401); // アクセス拒否されることを確認
    }
}
