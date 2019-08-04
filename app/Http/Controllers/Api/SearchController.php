<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ArticleOnlyResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Http\Resources\ArticleResource;

class SearchController extends Controller
{
    public function article(Request $request)
    {
        $articles = Article::search($request->keyword)->get();
        return ArticleOnlyResource::collection($articles);
    }
}
