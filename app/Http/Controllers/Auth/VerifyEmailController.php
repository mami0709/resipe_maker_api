<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use App\Providers\RouteServiceProvider;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request)
    {
        $token = $request->query('token');
        $user = User::where('email_verification_token', $token)->first();

        // トークンが無効、または期限切れの場合
        if (!$user || now()->isAfter($user->email_verification_token_expires_at)) {
            return response()->json(['message' => 'Token is invalid or expired'], 410);
        }

        // 認証済みかチェック
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return response()->json(['message' => 'Email verification successful'], 200);
    }
}
