<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * パスワードリセットリンクの送信テスト
     */
    public function test_send_password_reset_link(): void
    {
        Notification::fake();
        $user = User::firstOrFail();

        $response = $this->post('/api/password/email', ['email' => $user->email]);

        $response->assertStatus(200);

        Notification::assertSentTo(
            [$user],
            PasswordResetNotification::class
        );
    }

    /**
     * パスワードリセット機能テスト
     */
    public function test_password_reset(): void
    {
        Notification::fake();
        $user = User::firstOrFail();

        // パスワードリセットトークンと有効期限を設定
        $token = Str::random(60);
        $user->password_reset_token = hash('sha256', $token);
        $user->password_reset_token_expires_at = now()->addHours(24);
        $user->save();

        $newPassword = 'NewPassword123';
        $response = $this->post('/api/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);

        $response->assertStatus(200);

        // パスワードが更新されたか確認
        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

    /**
     * 無効なメールアドレスでのパスワードリセットリクエストのテスト
     */
    public function test_password_reset_request_with_invalid_email(): void
    {
        Notification::fake();

        $response = $this->post('/api/password/email', ['email' => 'invalid@example.com']);

        $response->assertStatus(404);

        Notification::assertNothingSent();
    }

    /**
     * 期限切れトークンでのパスワードリセットのテスト
     */
    public function test_password_reset_with_expired_token(): void
    {
        $user = User::firstOrFail();

        // 期限切れのトークンを設定
        $expiredToken = Str::random(60);
        $user->password_reset_token = hash('sha256', $expiredToken);
        $user->password_reset_token_expires_at = now()->subHours(24);
        $user->save();

        $response = $this->post('/api/password/reset', [
            'token' => $expiredToken,
            'email' => $user->email,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123'
        ]);

        $response->assertStatus(400);
    }
}
