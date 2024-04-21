<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserGetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ユーザー情報を取得できることを確認
     */
    public function test_get_user_info(): void
    {
        $user = User::firstOrFail();
        $this->actingAs($user);

        $response = $this->get('/api/users/' . $user->id);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'name_kana' => $user->name_kana,
                'role' => 10,
                'graduation_term' => $user->graduation_term,
                'nickname' => $user->nickname,
            ]);
    }

    /**
     * 無効なユーザーIDを指定した場合は404ステータス
     */
    public function test_invalid_user_id(): void
    {
        $user = User::firstOrFail();
        $this->actingAs($user);

        // 無効なユーザーIDでAPIにGETリクエストを送信
        $response = $this->get("/api/users/9999");

        $response->assertStatus(404);
    }
}
