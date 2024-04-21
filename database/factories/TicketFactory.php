<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\TicketStatusEnum;
use App\Enums\CategoryEnum;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        $statusNos = [
            TicketStatusEnum::Draft->value,
            TicketStatusEnum::Published->value,
            TicketStatusEnum::Closed->value
        ];
        $categoryIds = array_map(fn ($cat) => $cat->value, CategoryEnum::cases());

        return [
            'user_id' => User::factory(),
            'category_id' => $categoryIds[array_rand($categoryIds)],
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'status_no' => $statusNos[array_rand($statusNos)],
            'is_recruitment' => true,
        ];
    }
}
