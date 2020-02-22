<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Article;
use Faker\Generator as Faker;

$factory->define(Article::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'slug' => $faker->unique()->slug,
        'content' => $faker->paragraph,
        'seen_count' => 0,
    ];
});
