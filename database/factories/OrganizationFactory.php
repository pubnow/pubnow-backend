<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models;
use Faker\Generator as Faker;

$factory->define(Models\Organization::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->name,
        'slug' => $faker->unique()->word,
        'email' => $faker->unique()->email,
        'description' => $faker->sentence,
        'active' => false,
    ];
});
