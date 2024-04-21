<?php

namespace App\UseCases\Users;

use Illuminate\Http\JsonResponse;

final class CheckEmailVerificationAction
{
    public function __invoke(): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['errorMessage' => 'User not authenticated'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = auth()->user();

        // ユーザーのメールが認証されているか確認
        if (is_null($user->email_verified_at)) {
            return response()->json(['errorMessage' => 'Email not verified'], JsonResponse::HTTP_FORBIDDEN);
        }

        // 認証されている場合、ユーザーIDを返す
        return response()->json(['userId' => $user->id], JsonResponse::HTTP_OK);
    }
}
