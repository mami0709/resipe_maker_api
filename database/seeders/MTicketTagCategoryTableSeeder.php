<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MTicketTagCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tagCategories = [
            ['id' => 1, 'name' => 'フロントエンド', 'sort_no' => 1],
            ['id' => 2, 'name' => 'バックエンド', 'sort_no' => 2],
            ['id' => 3, 'name' => '言語', 'sort_no' => 3],
            ['id' => 4, 'name' => 'インフラ', 'sort_no' => 4],
            ['id' => 5, 'name' => '質問', 'sort_no' => 5],
            ['id' => 6, 'name' => 'その他', 'sort_no' => 6],
        ];

        DB::table('m_ticket_tag_category')->insert($tagCategories);
    }
}
