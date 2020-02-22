<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Category;
use Faker\Generator as Faker;

$factory->define(Category::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->name,
        'slug' => $faker->unique()->word,
        'description' => $faker->sentence,
    ];
});
