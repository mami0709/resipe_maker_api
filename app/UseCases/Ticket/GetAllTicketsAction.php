<?php

namespace App\UseCases\Ticket;

use App\Models\Ticket;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\TicketStatusEnum;

final class GetAllTicketsAction
{
    public function execute($category, int $perPage = 15, $page = null): LengthAwarePaginator
    {
        $query = Ticket::query();

        // status_no が公開中(Published)のチケットのみ取得
        $query->where('status_no', TicketStatusEnum::Published->value);

        if ($category !== 0 && $category !== null) {
            $query->where('category_id', $category);
        }

        $query->orderByDesc('id');

        $query->with(['user' => function ($query) {
            $query->select('id', 'nickname');
        }]);

        // $page を int にキャスト(null の場合は null のまま)
        $page = is_null($page) ? $page : (int)$page;

        // ページネーション
        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}
