<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Series;
use Faker\Generator as Faker;

$factory->define(Series::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'slug' => $faker->unique()->slug,
        'content' => $faker->paragraph,
    ];
});
