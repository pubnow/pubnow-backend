<?php

namespace App\Http\Controllers\Api;

use App\AdminStatistical;
use App\Http\Resources\AdminStatisticalResource;
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
        if(!$params || !array_key_exists("start_date", $params) || !array_key_exists("end_date", $params)) {
            return response()->json([
                "errors" => "Bad request."
            ], 403);
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
                'newUsersCount' => $usersCount,
                'allUsersCount' => $userCountAll,
                'newTagsCount' => $tagsCount,
                'allTagsCount' => $tagsCountAll,
                'newArticlesCount' => $articlesCount,
                'allArticlesCount' => $articlesCountAll,
                'newCategoriesCount' => $categoryCount,
                'allCategoriesCount' => $categoryCountAll,
                'highlightMember' => new UserResource($highlightMember),
                'highlightArticle' => $highlightArticle,
            ]]);
    }

    private function countUserRegisterScope($from, $to) {
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
        $highlight = $users->sortBy(function ($user) use ($users) {
            dd($user->articles->sum('seen_count'));
            return $user->articles->count();
//                + $user->articles->claps->sum('count') + $user->articles->comments->sum('count');
        })->reverse()->first();
        return $highlight;
    }

    private function getHighlightArticle()
    {
//        $articles = Article::all();
//        $highlight = $articles->sortBy(function ($article) use ($articles) {
//            return $article->claps->sum('count') + $article->comments->sum('count');
//        })->reverse()->first();
//        return $highlight;
        return 'x';
    }
}
