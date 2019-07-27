<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->uuid('owner');
            $table->foreign('owner')->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->string('name')->unique();
            $table->string('email')->unique();
            $table->string('description')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('active');
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
        Schema::dropIfExists('organizations');
    }
}
