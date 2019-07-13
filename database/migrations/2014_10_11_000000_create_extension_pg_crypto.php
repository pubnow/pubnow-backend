<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateExtensionPgCrypto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto";');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    { }
}
