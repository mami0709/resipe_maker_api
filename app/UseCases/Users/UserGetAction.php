<?php

namespace App\UseCases\Users;

use App\Models\User;

final class UserGetAction
{
    public function __invoke(int $userId): ?User
    {
        return User::find($userId);
    }
}
