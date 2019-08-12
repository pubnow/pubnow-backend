<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Article;
use App\Models\Category;
use App\Models\Feedback;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Feedback::class, function (Faker $faker) {
    $user = factory(User::class)->create();
    $category = factory(Category::class)->create();
    $article = factory(Article::class)->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);
    return [
        'article_id' => $article->id,
        'email' => $faker->unique()->safeEmail,
        'username' => $faker->unique()->userName,
        'reference' => $faker->sentence,
        'title' => $faker->sentence,
        'content' => $faker->paragraph,
        'type' => 0,
    ];
});
