<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Image;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Image::class, function (Faker $faker) {
    $user = factory(User::class)->create();
    return [
        'title' => $faker->title,
        'path' => $faker->url,
        'user_id' => $user->id,
    ];
});
