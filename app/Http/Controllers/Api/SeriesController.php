<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Series\CreateSeries;
use App\Http\Resources\ArticleOnlyResource;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\SeriesResource;
use App\Models\Article;
use App\Models\Category;
use App\Models\Series;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SeriesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])->except(['index', 'show']);
        $this->authorizeResource(Series::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $series = Series::latest()->paginate(10);
        return SeriesResource::collection($series);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateSeries $request)
    {
        $user = $request->user();
        $data = $request->only('title', 'content');

        $series = $user->series()->create([
            'title' => $data['title'],
            'content' => $data['content'],
            'slug' => str_slug($data['title']) . '-' . base_convert(time(), 10, 36),
        ]);

        $articles = $request->input('articles');
        if ($articles && !empty($articles)) {
            $listArticles = array_map(function ($item) {
                $article = Article::firstOrNew([
                    'id' => $item
                ]);
                if ($article) {
                    return $item;
                }
            }, $articles);
            $series->articles()->attach($listArticles);
        }

        return new SeriesResource($series);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Series  $series
     * @return \Illuminate\Http\Response
     */
    public function show(Series $series)
    {
        return new SeriesResource($series);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Series  $series
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Series $series)
    {
        $data = $request->only('title', 'content');
        if (array_key_exists("title", $data) && ($series->title !== $data['title'])) {
            $slug = str_slug($data['title']) . '-' . base_convert(time(), 10, 36);
            $data['slug'] = $slug;
        }

        $articles = $request->input('articles');
        $series->update($data);
        if ($articles && !empty($articles)) {
            $series->articles()->detach();
            if ($articles && !empty($articles)) {
                $listArticles = array_map(function ($item) {
                    return $item;
                }, $articles);
                $series->articles()->attach($listArticles);
            }
        }

        return new SeriesResource($series);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Series  $series
     * @return \Illuminate\Http\Response
     */
    public function destroy(Series $series)
    {
        $series->delete();
        return response()->json(null, 204);
    }

    public function articles(Series $series)
    {
        $articles = $series->articles;
        return ArticleOnlyResource::collection($articles);
    }
}
