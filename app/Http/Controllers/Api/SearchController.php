<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ArticleOnlyResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\User;
use App\Http\Resources\UserResource;

class SearchController extends Controller
{
    public function article(Request $request)
    {
        $articles = Article::search($request->keyword)->get();
        return ArticleOnlyResource::collection($articles);
    }

    public function user(Request $request)
    {
        $users = User::search($request->keyword)->get();
        return UserResource::collection($users);
    }
}
