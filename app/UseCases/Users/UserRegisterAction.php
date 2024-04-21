<?php

namespace App\UseCases\Users;

use App\Models\User;
use Illuminate\Support\Str;

final class UserRegisterAction
{
    public function __invoke(User $user): array
    {
        $user->role = config('consts.ROLE_USER');

        // トークンの生成と設定
        $token = Str::random(60);
        $user->email_verification_token = hash('sha256', $token);

        // トークンの有効期限を24時間に設定
        $user->email_verification_token_expires_at = now()->addHours(24);

        $user->save();

        // メール送信
        $user->sendEmailVerificationNotification();

        return $user->toArray();
    }
}
