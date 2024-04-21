<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\User;
use App\Notifications\CustomVerifyEmailNotification;
use Illuminate\Support\Facades\Notification;

class RegisterTest extends TestCase
{
    /**
     * 正常にユーザー登録できるか
     */
    public function test_register(): void
    {
        Notification::fake();

        $test_data = [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'Test1234',
            'remember_token' => Str::random(10),
            'name_kana' => 'テスト',
            'role' => config('consts.ROLE_USER'),
            'graduation_term' => 99,
            'nickname' => 'Test Nickname'
        ];
        $response = $this->post("/api/register", $test_data);
        // ログイン後、ユーザー情報を返す
        $response->assertStatus(200)
            ->assertJson([
                'name' => $test_data['name'],
                'email' => $test_data['email'],
                'name_kana' => $test_data['name_kana'],
                'role' => $test_data['role'],
                'graduation_term' => $test_data['graduation_term'],
                'nickname' => $test_data['nickname']
            ]);

        // ユーザーの取得
        $user = User::where('email', $test_data['email'])->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verification_token);

        // メールが送信されたことを確認
        Notification::assertSentTo(
            [$user],
            CustomVerifyEmailNotification::class
        );

        // メール認証プロセスのシミュレーション
        $response = $this->get("/api/verify-email?token={$user->email_verification_token}");
        $response->assertStatus(200);

        // メール認証が完了したことを確認
        $user = $user->fresh();
        $this->assertNotNull($user->email_verified_at);
    }

    /**
     * 必須フィールドが欠けている場合は400エラー
     */
    public function test_missing_required_fields(): void
    {
        $test_data = [];
        $response = $this->post("/api/register", $test_data);
        $response->assertStatus(400);
    }

    /**
     *  不正なデータを送信した場合は400エラー
     */
    public function test_register_with_invalid_data(): void
    {
        $response = $this->post("/api/register", [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'short',
            'name_kana' => 'テスト',
            'role' => config('consts.ROLE_USER'),
            'graduation_term' => 99,
            'nickname' => 'Test Nickname'
        ]);

        $response->assertStatus(400);
    }

    /**
     *  既に存在するメールアドレスの場合は400エラー
     */
    public function test_register_with_existing_email(): void
    {
        $existingUser = User::factory()->create();

        $response = $this->post("/api/register", [
            'name' => 'Another User',
            'email' => $existingUser->email,
            'password' => 'Test1234',
            'name_kana' => 'テスト',
            'role' => config('consts.ROLE_USER'),
            'graduation_term' => 99,
            'nickname' => 'Test Nickname'
        ]);

        $response->assertStatus(400);
    }

    /**
     *  無効な認証トークンの場合は410エラー
     */
    public function test_verification_with_invalid_token_redirects_to_login(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $invalidToken = 'invalid-token';
        $response = $this->get("/api/verify-email?token={$invalidToken}");

        $response->assertStatus(410);
    }


    /**
     *  既に認証されたメールアドレスの再認証テスト(302を返すか)
     */
    public function test_verification_of_already_verified_email(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(), // 既に認証されたユーザーを作成
        ]);

        $response = $this->get("/api/verify-email?token={$user->email_verification_token}");

        $response->assertStatus(200);
    }

    /**
     *  認証リンクの有効期限が切れている場合は410
     */
    public function test_verification_link_expiration(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
            'email_verification_token_expires_at' => now()->subDay(), // 期限切れに設定
        ]);

        $expiredToken = $user->email_verification_token;

        $response = $this->get("/api/verify-email?token={$expiredToken}");

        $response->assertStatus(410);
    }

    /**
     * 認証メールの再送信テスト
     */
    public function test_resend_verification_email(): void
    {
        Notification::fake();

        // 未認証のユーザーを作成
        $unverifiedUser = User::factory()->unverified()->create();

        // 認証メールを再送信
        $response = $this->post('/api/email/resend-verification', ['email' => $unverifiedUser->email]);

        // レスポンスの確認
        $response->assertStatus(200)
            ->assertJson(['status' => 'verification-link-sent']);

        // メールが再送信されたことを確認
        Notification::assertSentTo(
            [$unverifiedUser],
            CustomVerifyEmailNotification::class
        );
    }

    /**
     * 再送信した認証メールからの認証完了テスト
     */
    public function test_verify_email_after_resending_verification_link(): void
    {
        Notification::fake();

        $unverifiedUser = User::factory()->unverified()->create();

        // 認証メールを再送信
        $this->post('/api/email/resend-verification', ['email' => $unverifiedUser->email]);

        // メールが再送信されたことを確認
        Notification::assertSentTo(
            [$unverifiedUser],
            CustomVerifyEmailNotification::class,
            function ($notification) use (&$token, $unverifiedUser) {
                $mailData = $notification->toMail($unverifiedUser)->viewData;

                // ビューデータからトークンを取得
                parse_str(parse_url($mailData['url'], PHP_URL_QUERY), $queryParams);
                $token = $queryParams['token'];
                return true;
            }
        );

        $response = $this->get('/api/verify-email?token=' . $token);

        $response->assertStatus(200);

        // ユーザーが認証されたことを確認
        $this->assertTrue($unverifiedUser->fresh()->hasVerifiedEmail());
    }
}
