<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['id' => 1, 'text' => 'バグ・相談'],
            ['id' => 2, 'text' => 'セミナー・勉強会'],
            ['id' => 3, 'text' => 'イベント'],
            ['id' => 4, 'text' => '企業案件'],
            ['id' => 5, 'text' => '求人・採用'],
            ['id' => 6, 'text' => 'その他'],
        ];

        DB::table('m_ticket_category')->insert($categories);
    }
}
