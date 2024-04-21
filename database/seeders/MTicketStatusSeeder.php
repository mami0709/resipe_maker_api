<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MTicketStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            ['no' => 10, 'label' => '下書き'],
            ['no' => 20, 'label' => '公開中'],
            ['no' => 100, 'label' => 'クローズ']
        ];

        foreach ($statuses as $status) {
            DB::table('m_ticket_status')->insert([
                'no' => $status['no'],
                'label' => $status['label'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
