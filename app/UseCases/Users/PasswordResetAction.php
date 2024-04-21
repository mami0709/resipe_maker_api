<?php

namespace App\UseCases\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class PasswordResetAction
{
    public function __invoke($email, $token, $password): array
    {
        $user = User::where('email', $email)
            ->where('password_reset_token', hash('sha256', $token))
            ->first();

        if (!$user || now()->isAfter($user->password_reset_token_expires_at)) {
            return ['status' => 'error', 'message' => 'Invalid token'];
        }

        $user->password = Hash::make($password);
        $user->password_reset_token = null;
        $user->password_reset_token_expires_at = null;
        $user->save();

        return ['status' => 'success', 'message' => 'Password has been reset'];
    }
}
