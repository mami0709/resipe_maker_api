<?php

declare(strict_types=1);

namespace App\UseCases\Ticket;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

final class GetTicketsByUserIdAndCategoryAction
{
    public function __invoke($userId, $category, $page, $perPage): LengthAwarePaginator
    {
        // ユーザーの存在を確認
        $user = User::find($userId);
        if (is_null($user)) {
            throw new ModelNotFoundException('User not found');
        }

        // ユーザーに関連するチケットと、ユーザーが回答したチケットを取得
        $query = Ticket::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhereHas('answers', function ($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                });
        });

        // カテゴリ絞り込み
        if ($category !== null) {
            $query->where('category_id', $category);
        }

        // ページネーションを実行
        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}
