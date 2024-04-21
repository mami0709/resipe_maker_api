<?php

namespace App\UseCases\Users;

use App\Models\User;
use Illuminate\Support\Str;
use App\Notifications\PasswordResetNotification;

final class PasswordResetSendAction
{
    public function __invoke($email): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return ['status' => 'error', 'message' => 'Email address not found'];
        }

        $token = Str::random(60);
        $user->password_reset_token = hash('sha256', $token);
        $user->password_reset_token_expires_at = now()->addHours(24);
        $user->save();

        $user->notify(new PasswordResetNotification($token));

        return ['status' => 'success', 'message' => 'Password reset link has been sent'];
    }
}
