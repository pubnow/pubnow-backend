<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Model;
use App\Models\Category;
use App\Models\User;
use Faker\Generator as Faker;
use App\Models\Article;
use App\Models\Bookmark;

$factory->define(Bookmark::class, function (Faker $faker) {
    return [];
});
