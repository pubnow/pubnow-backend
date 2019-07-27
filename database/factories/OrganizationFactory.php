<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models;
use Faker\Generator as Faker;

$factory->define(Models\Organization::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->name,
        'email' => $faker->unique()->email,
        'description' => $faker->sentence,
        'logo' => $faker->imageUrl,
        'active' => false,
    ];
});
