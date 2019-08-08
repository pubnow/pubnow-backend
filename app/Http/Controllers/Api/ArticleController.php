<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Article\UpdateArticle;
use App\Http\Requests\Api\Bookmark\CreateBookmark;
use App\Http\Resources\ArticleOnlyResource;
use App\Http\Resources\BookmarkResource;
use App\Http\Resources\ClapResource;
use App\Http\Resources\CommentResource;
use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Organization;
use App\Models\Clap;
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
        $articles = Article::withAuthor()->orderByDesc('created_at')->paginate(10);
        return ArticleOnlyResource::collection($articles);
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
        if ($request->has('organization_id')) {
            $organization = Organization::find($request->get('organization_id'));
            if (!$organization->active) {
                return response()->json([
                    'errors' => [
                        'message' => 'Organization not activated',
                    ]
                ], 422);
            }
            if (!$organization->members->find($user->id)) {
                return response()->json([
                    'errors' => [
                        'message' => 'Not member of organization'
                    ]
                ], 403);
            }
        }

        $data = $request->only('title', 'content', 'category_id', 'draft', 'private', 'organization_id', 'organization_private');
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
        if ($article->private) {
            $can = true;
            if (!auth()->user()) {
                $can = false;
            } else {
                if (auth()->user()->isAdmin()) {
                    $can = true;
                } else {
                    if (!($article->author->id === auth()->user()->id)) {
                        $can = false;
                    }
                }
            }
            if (!$can) {
                return response()->json([
                    'message' => [
                        'Unauthorized',
                    ]
                ], 401);
            }
        }
        if ($article->organization_id && $article->organization_private) {
            if (!auth()->user()) {
                return response()->json([
                    'message' => [
                        'Unauthorized',
                    ]
                ], 401);
            }
            $user = auth()->user();
            $organization = Organization::find($article->organization_id);
            if (!$user->isAdmin() && !$organization->members->find($user->id)) {
                return response()->json([
                    'message' => [
                        'Only organization member can read this article',
                    ]
                ], 403);
            }
        }
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
        $articles = Article::withAuthor()->orderBy('seen_count', 'desc')->take(5)->get();
        return ArticleOnlyResource::collection($articles);
    }

    public function featured()
    {
        $articles = Article::withAuthor()->with('claps')->with('comments')->get()->sortBy(function ($article) {
            return $article->claps->sum('count') + $article->comments->count();
        })->reverse()->take(5);
        return ArticleOnlyResource::collection($articles);
    }

    public function comments(Article $article)
    {
        $comments = $article->comments()->where('parent_id', null)->get();
        return CommentResource::collection($comments);
    }

    public function clap(Request $request, Article $article)
    {
        $user = $request->user();
        $clap = Clap::firstOrNew([
            'user_id' => $user->id,
            'article_id' => $article->id,
        ]);
        if ($clap->count !== null) {
            $clap->update([
                'count' => $clap->count + 1,
            ]);
        } else {
            $clap->count = 1;
            $clap->save();
        }
        return new ClapResource($clap);
    }

    public function unclap(Request $request, Article $article)
    {
        $user = $request->user();
        $clap = Clap::where('user_id', $user->id)->where('article_id', $article->id)->firstOrFail();
        $clap->delete();
        return response()->json(null, 204);
    }
}
