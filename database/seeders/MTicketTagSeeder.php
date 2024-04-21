<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MTicketTag;
use App\Enums\TagCategoryEnum;

class MTicketTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tags = [
            ['label' => 'PHP', 'category_id' => TagCategoryEnum::Language->value],
            ['label' => 'Laravel', 'category_id' => TagCategoryEnum::Backend->value],
            ['label' => 'HTML', 'category_id' => TagCategoryEnum::Frontend->value],
            ['label' => 'CSS', 'category_id' => TagCategoryEnum::Frontend->value],
            ['label' => 'JavaScript', 'category_id' => TagCategoryEnum::Frontend->value],
            ['label' => 'Ruby', 'category_id' => TagCategoryEnum::Language->value],
            ['label' => 'React', 'category_id' => TagCategoryEnum::Frontend->value],
            ['label' => '質問', 'category_id' => TagCategoryEnum::Question->value],
            ['label' => 'アンケート', 'category_id' => TagCategoryEnum::Others->value],
            ['label' => '部活', 'category_id' => TagCategoryEnum::Others->value],
            ['label' => 'ポートフォリオ', 'category_id' => TagCategoryEnum::Others->value],
            ['label' => 'エラー', 'category_id' => TagCategoryEnum::Others->value],
            ['label' => 'AWS', 'category_id' => TagCategoryEnum::Infrastructure->value],
        ];

        foreach ($tags as $tag) {
            MTicketTag::create($tag);
        }
    }
}
