<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Article\UpdateArticle;
use App\Http\Requests\Api\Bookmark\CreateBookmark;
use App\Http\Resources\BookmarkResource;
use App\Http\Resources\CommentResource;
use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Tag;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Http\Requests\Api\Article\CreateArticle;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])->except(['index', 'show', 'popular', 'featured', 'comments']);
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
        $data = $request->only('title', 'content', 'category_id', 'draft', 'private');
        $article = $user->articles()->create(array_merge($data, [
            'seen_count' => 0,
            'slug' => str_slug($data['title']) . '-' . base_convert(time(), 10, 36),
        ]));
        $inputTags = $request->input('tags');
        if ($inputTags && !empty($inputTags)) {
            $tags = array_map(function ($name) {
                $tag = Tag::firstOrNew([
                    'name' => $name,
                ]);
                if (!$tag->slug) {
                    $tag->slug =  str_slug($name) . '-' . base_convert(time(), 10, 36);
                    $tag->save();
                }
                return $tag->id;
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
        $data = $request->only('title', 'content', 'category_id', 'draft', 'private', 'organization_private');

        $article->update($data);

        $article->tags()->detach();

        $inputTags = $request->input('tags');
        if ($inputTags && !empty($inputTags)) {
            $tags = array_map(function ($name) {
                $tag = Tag::firstOrNew([
                    'name' => $name,
                ]);
                if (!$tag->slug) {
                    $tag->slug =  str_slug($name) . '-' . base_convert(time(), 10, 36);
                    $tag->save();
                }
                return $tag->id;
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

    private function filterShowArticle()
    {
        $user = auth()->user();
        $articles = Article::where('draft', false);
        // chưa đăng nhập trả về những bài non-draft và non-private
        if (!$user) {
            $userArticles = $articles
                ->where('organization_id', null)
                ->where('private', false);
            $organizationArticles = $articles
                ->where('organization_id', '<>', null)
                ->where('organization_private', false);
            $articles = $userArticles->union($organizationArticles)
                ->orderByDesc('created_at')
                ->paginate(10);
            return $articles;
        }
        // đã đăng nhập thì trả về những bài non-draft và bài its private
        // lấy những bài private nhưng đúng tác giả
        $userPrivateArticles = $user->articles()->where('organization_id', null)->where('private', true);
        $userNonPrivateArticles = $articles->where('organization_id', null)->where('private', false);

        $organizationPrivateArticles = $user->organizations()->articles()->where('organization_private', true);
        $organizationNonPrivateArticles = $articles->where('organization_id', '<>', null)->where('organization_private', false);

        $articles = $userNonPrivateArticles
            ->union($userPrivateArticles)
            ->union($organizationPrivateArticles)
            ->union($organizationNonPrivateArticles);
        $articles = $articles
            ->orderByDesc('created_at')
            ->paginate(10);
        return $articles;
    }

    public function featured() {
        $articles = Article::with('claps')->with('comments')->get()->sortBy(function ($article) {
            return $article->claps->sum('count') + $article->comments->count();
        })->reverse()->take(5);
        return ArticleResource::collection($articles);
    }

    public function comments(Article $article) {
        $comments = $article->comments()->where('parent_id', null)->get();
        return CommentResource::collection($comments);
    }
}
