<?php

namespace App\UseCases\Users;

use App\Models\User;

final class UserDeleteAction
{
    public function __invoke(int $targetUserId, int $authUserId): bool
    {
        if ($targetUserId !== $authUserId) {
            // IDが一致しない場合は削除操作を許可しない。
            abort(403, 'Unauthorized action. Cannot delete another user.');
        }

        $user = User::find($targetUserId);

        if (is_null($user)) {
            // 対象のユーザーが見つからない場合はエラーを返す。
            abort(404, 'User not found');
        }

        // ユーザーを削除
        return $user->delete();
    }
}
