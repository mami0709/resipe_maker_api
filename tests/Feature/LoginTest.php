<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CustomVerifyEmailNotification;


class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証完了後、正常にログインできるか
     */
    public function test_login_with_verified_email(): void
    {
        Mail::fake();

        $user = User::factory()->create(['password' => bcrypt('Test1234'), 'email_verified_at' => now()]);
        // ログインする
        $response = $this->post("/api/login", ['email' => $user->email, 'password' => 'Test1234']);
        $response->assertStatus(200);
        // このユーザーがログイン認証されているか
        $this->assertAuthenticatedAs($user);
        // レスポンスに含まれるユーザー情報が正しいことを確認
        $response->assertJson([
            'id' => $user->id,
            'email' => $user->email,
            'nickname' => $user->nickname
        ]);
    }

    /**
     * メール認証がまだの場合はエラーを表示し、認証メールを再送信されるか
     */
    public function test_login_with_unverified_email(): void
    {
        Notification::fake();

        // メールが未認証のユーザーを作成
        $user = User::factory()->create([
            'password' => bcrypt('Test1234'),
            'email_verified_at' => null
        ]);

        // ログイン
        $response = $this->post("/api/login", ['email' => $user->email, 'password' => 'Test1234']);
        $response->assertStatus(401)
            ->assertJson(['message' => 'メールが認証されていません。認証リンクが再送されました。']);
        // 認証メールが再送されることを確認
        Notification::assertSentTo(
            [$user],
            CustomVerifyEmailNotification::class
        );
    }


    /**
     * 入力情報が違う場合は401エラーと指定のエラー文言
     */
    public function test_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->post("/api/login", [
            'email' => $user->email,
            'password' => 'invalidpassword'
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'メールアドレスまたはパスワードが違います']);
    }

    /**
     * ユーザーが存在しない場合は401エラーと指定のエラー文言
     */
    public function test_user_does_not_exist(): void
    {
        $response = $this->post("/api/login", [
            'email' => 'nonexistent@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'ユーザーが存在しません']);
    }
}
