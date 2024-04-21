<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateMTicketTagCategoryTable extends Migration
{
    public function up()
    {
        Schema::create('m_ticket_tag_category', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('sort_no');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('m_ticket_tag_category');
    }
}
