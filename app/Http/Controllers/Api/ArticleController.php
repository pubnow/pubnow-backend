<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Article\UpdateArticle;
use App\Models\Article;
use App\Models\Tag;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Http\Requests\Api\Article\CreateArticle;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])->except(['index', 'show']);
        $this->authorizeResource(Article::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $articles = $this->filterShowArticle();
        return ArticleResource::collection($articles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateArticle $request)
    {
        $user = $request->user();
        $data = $request->only('title', 'content', 'category');
        $article = $user->articles()->create([
            'title' => $data['title'],
            'content' => $data['content'],
            'category_id' => $data['category'],
            'seen_count' => 0,
            'slug' => str_slug($data['title']) . '-' . base_convert(time(), 10, 36),
            'draft' => $request->draft,
            'private' => $request->private
        ]);
        $inputTags = $request->input('tags');
        if ($inputTags && !empty($inputTags)) {
            $tags = array_map(function ($name) {
                return Tag::firstOrCreate([
                    'name' => $name,
                    'slug' => str_slug($name) . '-' . base_convert(time(), 10, 36)
                ])->id;
            }, $inputTags);
            $article->tags()->attach($tags);
        }
        return new ArticleResource($article);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        $article->update([
            'seen_count' => $article->seen_count + 1
        ]);
        return new ArticleResource($article);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateArticle $request, Article $article)
    {
        $data = $request->only('title', 'content', 'category', 'draft', 'private');

        $article->update($data);

        $article->tags()->detach();

        $inputTags = $request->input('tag_list');
        if ($inputTags && !empty($inputTags)) {
            $tags = array_map(function ($name) {
                return Tag::firstOrCreate([
                    'name' => $name,
                    'slug' => str_slug($name) . '-' . base_convert(time(), 10, 36)
                ])->id;
            }, $inputTags);
            $article->tags()->attach($tags);
        }

        return new ArticleResource($article);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {
        $article->delete();
        return response()->json(null, 204);
    }

    public function popular()
    {
        $articles = Article::orderBy('seen_count', 'desc')->take(5)->get();
        return ArticleResource::collection($articles);
    }

    private function filterPrivateAricle(Article $articles)
    {
        $articles = $articles
            ->where('private', true);
        return $articles;
    }

    private function filterShowArticle() {
        $articles = Article::where('draft', null)
            ->orWhere('draft', false)
            ->where('author.id', '2403dabb-2de4-4ce7-a954-e19bf4070ee5')
//            ->where('private', null)
//            ->orWhere('private', false)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return $articles;
    }
}
