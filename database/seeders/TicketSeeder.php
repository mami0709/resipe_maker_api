<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\User;
use App\Enums\TicketStatusEnum;
use App\Enums\CategoryEnum;

class TicketSeeder extends Seeder
{
    public function run()
    {
        // 既存の全ユーザーを取得
        $users = User::all();

        // 各ユーザーごとに1~5の間でランダムな数のチケットを作成
        foreach ($users as $user) {
            $ticketsCount = rand(1, 5);

            $statusValues = array_map(fn ($status) => $status->value, TicketStatusEnum::cases());
            $categoryIds = array_map(fn ($cat) => $cat->value, CategoryEnum::cases());

            for ($i = 0; $i < $ticketsCount; $i++) {
                Ticket::factory()->create([
                    'user_id' => $user->id,
                    'category_id' => $categoryIds[array_rand($categoryIds)],
                    'status_no' => $statusValues[array_rand($statusValues)]
                ]);
            }
        }
    }
}
