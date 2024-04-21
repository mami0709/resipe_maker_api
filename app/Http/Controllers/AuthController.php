<?php

namespace App\Http\Controllers;

use App\UseCases\Users\UserGetAction;
use App\Http\Requests\UserDeleteRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use App\UseCases\Users\UserDeleteAction;
use App\UseCases\Users\UserRegisterAction;
use App\UseCases\Users\UserUpdateAction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\UseCases\Users\PasswordResetSendAction;
use App\UseCases\Users\PasswordResetAction;
use App\UseCases\Users\UserLoginAction;
use App\UseCases\Users\CheckEmailVerificationAction;

class AuthController extends Controller
{
    public function register(UserRegisterRequest $request, UserRegisterAction $action): JsonResponse
    {
        return response()->json($action($request->makeUser()));
    }

    public function login(UserLoginRequest $request, UserLoginAction $action): JsonResponse
    {
        return $action($request);
    }

    public function getUser(UserGetAction $action, int $userId): JsonResponse
    {
        $user = $action($userId);
        if (is_null($user)) {
            return response()->json(['message' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        return response()->json($user);
    }

    public function delete(UserDeleteRequest $request, UserDeleteAction $action, int $userId): JsonResponse
    {
        $targetUser = User::find($userId);
        if (is_null($targetUser)) {
            return response()->json(['message' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $result = $action($userId, $request->user()->id);
        if ($result) {
            return response()->json(['message' => 'User successfully deleted'], JsonResponse::HTTP_OK);
        } else {
            return response()->json(['message' => 'User deletion failed'], JsonResponse::HTTP_FORBIDDEN);
        }
    }

    public function update(UserUpdateRequest $request, UserUpdateAction $action, int $userId): JsonResponse
    {
        if ($userId !== $request->user()->id) {
            return response()->json(['error' => 'Cannot update another user'], JsonResponse::HTTP_FORBIDDEN);
        }

        $user = $action($userId, $request->validated());
        return response()->json($user);
    }

    public function logout(Request $request): JsonResponse
    {
        // ユーザーが認証されていない場合はエラーを返す
        if (!auth()->check()) {
            return response()->json(['error' => 'User is already logged out'], JsonResponse::HTTP_BAD_REQUEST);
        }
        // セッションを終了させる
        auth()->logout();
        return response()->json(['message' => 'Logged out successfully'], JsonResponse::HTTP_OK);
    }

    public function getUserInfo(Request $request)
    {
        return $request->user();
    }

    // PWリセット
    public function sendResetLinkEmail(Request $request, PasswordResetSendAction $action): JsonResponse
    {
        $request->validate(['email' => 'required|email']);
        $result = $action($request->email);

        return response()->json(['message' => $result['message']], $result['status'] === 'success' ? 200 : 404);
    }

    public function reset(Request $request, PasswordResetAction $action): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
        ]);

        $result = $action($request->email, $request->token, $request->password);

        return response()->json(['message' => $result['message']], $result['status'] === 'success' ? 200 : 400);
    }

    public function checkAuth()
    {
        $action = new CheckEmailVerificationAction();
        return $action();
    }
}
