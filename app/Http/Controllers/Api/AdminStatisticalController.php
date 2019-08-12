<?php

namespace App\Http\Controllers\Api;

use App\AdminStatistical;
use App\Http\Resources\AdminStatisticalResource;
use App\Http\Resources\ArticleOnlyResource;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\UserResource;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminStatisticalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function statisticalUserRegister(Request $request)
    {
        $this->authorize('showStatistic', User::class);
        $params = $request->only(['start_date', 'end_date']);
        if (!$params || !array_key_exists("start_date", $params) || !array_key_exists("end_date", $params)) {
            return response()->json([
                "errors" => "Internal Server Error"
            ], 500);
        }
        $from = date($params['start_date']);
        $to = date($params['end_date']);
        $usersCount = $this->countUserRegisterScope($from, $to);
        $userCountAll = $this->statisticalUserRegisterAll();

        $tagsCount = $this->getNewTagsCount($from, $to);
        $tagsCountAll = $this->getTagsCount();

        $articlesCount = $this->getNewArticlesCount($from, $to);
        $articlesCountAll = $this->getArticlesCount();

        $categoryCount = $this->getNewCategoriesCount($from, $to);
        $categoryCountAll = $this->getCategoriesCount();

        $highlightMember = $this->getHighlightMember();
        $highlightArticle = $this->getHighlightArticle();

        return response()->json([
            'data' => [
                'from' => $from,
                'to' => $to,
                'users' => [
                    'new' => $usersCount,
                    'total' => $userCountAll
                ],
                'tags' => [
                    'new' => $tagsCount,
                    'total' => $tagsCountAll
                ],
                'articles' => [
                    'new' => $articlesCount,
                    'total' => $articlesCountAll
                ],
                'categories' => [
                    'new' => $categoryCount,
                    'total' => $categoryCountAll
                ],
                'featuredMember' => [
                    'total_articles' => $highlightMember->articles->count(),
                    'data' => new UserResource($highlightMember),
                ],
                'featuredArticle' => [
                    'total_claps' => $highlightArticle->claps->sum('count'),
                    'total_comments' => $highlightArticle->comments->count(),
                    'data' => new ArticleOnlyResource($highlightArticle),
                ],
            ]
        ]);
    }

    private function countUserRegisterScope($from, $to)
    {
        $usersCount = User::whereBetween('created_at', [$from, $to])->get()->count();
        return $usersCount;
    }

    private function statisticalUserRegisterAll()
    {
        return User::all()->count();
    }

    private function getNewArticlesCount($from, $to)
    {
        $articleCount = Article::whereBetween('created_at', [$from, $to])->get()->count();
        return $articleCount;
    }

    private function getArticlesCount()
    {
        $articleCount = Article::all()->count();
        return $articleCount;
    }

    private function getNewTagsCount($from, $to)
    {
        $tagsCount = Tag::whereBetween('created_at', [$from, $to])->get()->count();
        return $tagsCount;
    }

    private function getTagsCount()
    {
        $tagsCount = Tag::all()->count();
        return $tagsCount;
    }

    private function getNewCategoriesCount($from, $to)
    {
        $tagsCount = Category::whereBetween('created_at', [$from, $to])->get()->count();
        return $tagsCount;
    }

    private function getCategoriesCount()
    {
        $tagsCount = Category::all()->count();
        return $tagsCount;
    }

    private function getHighlightMember()
    {
        $users = User::all();
        $highlight = $users->sortByDesc(function ($user) use ($users) {
            return $user->articles->count();
        })->first();
        return $highlight;
    }

    private function getHighlightArticle()
    {
        $articles = Article::all();
        $highlight = $articles->sortByDesc(function ($article) use ($articles) {
            return $article->claps->sum('count') + $article->comments->count();
        })->first();
        return $highlight;
    }
}
