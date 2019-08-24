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
use Illuminate\Support\Facades\DB;
use DatePeriod;

class AdminStatisticalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function statisticalUserRegister(Request $request)
    {
        $this->authorize('showStatistic', User::class);
        $params = $request->only(['start', 'end']);
        if (!$params || !array_key_exists("start", $params) || !array_key_exists("end", $params)) {
            return response()->json([
                "errors" => "Internal Server Error"
            ], 500);
        }
        $from = date($params['start']);
        $to = date($params['end']);
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
                'articles_by_category' => $this->roundChart(),
                'articles_by_days' => $this->lineChart($request),
                'featuredMember' => new UserResource($highlightMember),
                'featuredArticle' => new ArticleOnlyResource($highlightArticle),
            ]
        ]);
    }

    private function roundChart()
    {
        $articlesByCategories = Article::select('category_id', DB::raw('count(*) as count'))
            ->groupBy('category_id')
            ->orderBy('count', 'DESC')
            ->get();

        $roundChartData = collect($articlesByCategories)->map(function ($articlesByCategory) {
            $category = Category::find($articlesByCategory->category_id);
            return [
                'category' => $category,
                'count' => $articlesByCategory->count
            ];
        });
        return $roundChartData;
    }

    private function lineChart($request)
    {
        $start = date($request->input('start'));
        $end = strtotime("1 day", strtotime($request->input('end')));
        $end = date("Y-m-d", $end);
        $articlesByDay = Article::whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        $articlesByDay = collect($articlesByDay)->keyBy('date')->map(function ($item) {
            $item->date = \Carbon\Carbon::parse($item->date);
            return $item;
        });
        $s = new \Carbon\Carbon($request->input('start'));
        $e = new \Carbon\Carbon($request->input('end'));
        $e->addDay();
        $periods = new DatePeriod(
            $s,
            \Carbon\CarbonInterval::day(),
            $e
        );
        $temp = array_map(function ($period) use ($articlesByDay) {
            $day = $period->format('Y-m-d');
            return (object) [
                'date' => $day,
                'count' => $articlesByDay->has($day) ? $articlesByDay->get($day)->count : 0,
            ];
        }, iterator_to_array($periods));
        return $temp;
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
