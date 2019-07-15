<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Http\Resources\ArticleResource;

class SearchController extends Controller
{
    public function query(Request $request)
    {
        $articles = Article::search($request->search)->get();
        return ArticleResource::collection($articles);
    }
}
