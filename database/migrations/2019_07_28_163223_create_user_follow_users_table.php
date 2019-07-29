<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades;

class CreateUserFollowUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_follow_users', function (Blueprint $table) {
            $table->uuid('id')->default(DB::raw('gen_random_uuid()'))->primary('id');

            // id of user who follow other user
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');

            // id of user who be followed
            $table->uuid('followed');
            $table->foreign('followed')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->unique(['user_id', 'followed']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_follow_users');
    }
}
