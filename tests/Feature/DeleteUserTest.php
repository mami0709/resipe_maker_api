<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteUserTest extends TestCase
{
    use RefreshDatabase;

    /*
    　正常にユーザー削除できるか
    */
    public function test_delete_user(): void
    {
        // ユーザー取得
        $user = User::firstOrFail();

        // ユーザー認証
        $this->actingAs($user);
        $response = $this->delete("/api/users/{$user->id}");

        $response->assertStatus(200)->assertJson(['message' => 'User successfully deleted']);
        $this->assertSoftDeleted('user', ['id' => $user->id]); // ユーザーがSoftDeletedされたことを確認
    }

    /*
    　無効なユーザーIDを指定した場合は404
    */
    public function test_invalid_user_id_for_deletion(): void
    {
        $user = User::firstOrFail();
        $this->actingAs($user);

        $response = $this->delete("/api/users/0");

        $response->assertStatus(404);
    }

    /*
    　退会後、同じメールアドレスを持つ新しいユーザーを作成することができるか
    */
    public function test_recreate_user_with_same_email_after_deletion(): void
    {
        // 最初のユーザーを取得
        $user = User::firstOrFail();

        // 論理削除前のexistカラムの値を確認
        $this->assertDatabaseHas('user', ['email' => $user->email, 'exist' => 1]);

        // ユーザーを認証して削除
        $this->actingAs($user);
        $this->delete("/api/users/{$user->id}");

        // 論理削除後のexistカラムの値を確認
        $this->assertDatabaseHas('user', ['email' => $user->email, 'exist' => null]);

        // 同じメールアドレスを持つ新しいユーザーを作成
        $newUser = User::factory()->create(['email' => $user->email]);

        // 新しいユーザーのexistカラムの値を確認
        $this->assertDatabaseHas('user', ['email' => $user->email, 'id' => $newUser->id, 'exist' => 1]);
    }
}
