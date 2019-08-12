<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetArticleIdNullableForFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feedback', function (Blueprint $table) {
            if (Schema::hasColumn('feedback', 'article_id')) {
                $table->dropForeign(['article_id']);
                $table->dropColumn(['article_id']);
            }

            $table->uuid('id_article')->nullable();
            $table->foreign('id_article')->references('id')->on('articles')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->dropForeign(['id_article']);
            $table->dropColumn(['id_article']);
        });
    }
}
