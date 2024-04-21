<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_kana');
            $table->string('email');
            $table->string('email_verification_token')->nullable();
            $table->dateTime('email_verified_at')->nullable();
            $table->string('password');
            $table->string('password_reset_token')->nullable();
            $table->dateTime('password_reset_token_expires_at')->nullable();
            $table->string('nickname')->nullable();
            $table->string('icon')->nullable();
            $table->integer('role');
            $table->integer('graduation_term')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->dateTime('email_verification_token_expires_at')->nullable();

            // created_at と updated_at を dateTime 型で明示的に定義
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            // deleted_at も dateTime 型で明示的に定義
            $table->dateTime('deleted_at')->nullable();
        });

        // 複合ユニーク制約
        Schema::table('user', function (Blueprint $table) {
            $table->boolean('exist')->nullable()->storedAs('case when deleted_at is null then 1 else null end');
            $table->unique(['email', 'exist']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
