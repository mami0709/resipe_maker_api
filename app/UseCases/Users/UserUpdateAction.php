<?php

namespace App\UseCases\Users;

use App\Models\User;

final class UserUpdateAction
{
    public function __invoke(int $userId, array $attributes): User
    {
        $user = User::findOrFail($userId);

        // もしpasswordが指定されていればハッシュ化
        if (isset($attributes['password'])) {
            $attributes['password'] = bcrypt($attributes['password']);
        }

        $user->update($attributes);
        return $user;
    }
}
