<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_their_information()
    {
        // 既存のユーザーを取得
        $user = User::firstOrFail();
        // ユーザーで認証
        $this->actingAs($user);

        // 更新データ
        $updateData = [
            'name' => 'UpdatedName',
            'email' => 'updated@example.com',
            'password' => 'newPassword123',
            'password_confirmation' => 'newPassword123',
            'name_kana' => 'UpdatedKana',
            'role' => 10,
            'graduation_term' => 100,
            'nickname' => 'Updated Nickname'
        ];

        $response = $this->put("/api/users/{$user->id}", $updateData);

        // レスポンスをアサート
        $response->assertStatus(200);

        // データベースのユーザー情報が更新されたことをアサート
        $this->assertDatabaseHas('user', [
            'id' => $user->id,
            'name' => 'UpdatedName',
            'email' => 'updated@example.com',
            'graduation_term' => 100,
            'nickname' => 'Updated Nickname'
        ]);
    }

    // 無効なユーザーIDを指定した場合は403
    public function test_invalid_user_id()
    {
        $user = User::firstOrFail();
        $this->actingAs($user);

        $response = $this->put("/api/users/9999", [
            'name' => 'NewName',
        ]);

        $response->assertStatus(403);
    }
}
