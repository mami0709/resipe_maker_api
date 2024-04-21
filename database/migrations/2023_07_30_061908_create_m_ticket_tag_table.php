<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateMTicketTagTable extends Migration
{
    public function up()
    {
        Schema::create('m_ticket_tag', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->unsignedBigInteger('category_id');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            // 外部キー制約
            $table->foreign('category_id')->references('id')->on('m_ticket_tag_category');
        });
    }

    public function down()
    {
        Schema::dropIfExists('m_ticket_tag');
    }
}
