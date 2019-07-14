<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Tag;
use Faker\Generator as Faker;

$factory->define(Tag::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->name,
        'slug' => $faker->unique()->slug,
        'description' => $faker->sentence,
        'image' => $faker->imageUrl,
    ];
});
