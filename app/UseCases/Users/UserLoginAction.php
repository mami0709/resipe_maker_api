<?php

namespace App\UseCases\Users;

use App\Http\Requests\UserLoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

final class UserLoginAction
{
    public function __invoke(UserLoginRequest $request): JsonResponse
    {
        // ユーザーが存在するかチェック
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return new JsonResponse(['message' => 'ユーザーが存在しません'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return new JsonResponse(['message' => 'メールアドレスまたはパスワードが違います'], JsonResponse::HTTP_UNAUTHORIZED);
        }
        /** @var User $user */
        $user = Auth::user();

        // メールアドレスの認証状態をチェック
        if (is_null($user->email_verified_at)) {
            // 認証メールを再送信
            $token = Str::random(60);
            $user->email_verification_token = hash('sha256', $token);
            $user->email_verification_token_expires_at = now()->addHours(24);
            $user->save();

            $user->sendEmailVerificationNotification();

            return new JsonResponse(['message' => 'メールが認証されていません。認証リンクが再送されました。'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // メールが認証済みの場合、ユーザー情報を返す
        return new JsonResponse([
            'id' => $user->id,
            'email' => $user->email,
            'nickname' => $user->nickname,
        ], JsonResponse::HTTP_OK);
    }
}
