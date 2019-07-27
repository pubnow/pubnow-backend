<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Model;
use App\Models\Category;
use App\Models\User;
use Faker\Generator as Faker;
use App\Models\Article;
use App\Models\Bookmark;

$factory->define(Bookmark::class, function (Faker $faker) {
    $user = factory(User::class)->create();
    $category = factory(Category::class)->create();
    $article = factory(Article::class)->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);
    return [
        'user_id' => $user->id,
        'article_id' => $article->id
    ];
});
