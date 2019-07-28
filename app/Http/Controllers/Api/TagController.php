<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Tag\UpdateTag;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\TagOnlyResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserWithFollowingTagsResource;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Http\Requests\Api\Tag\CreateTag;
use Illuminate\Support\Facades\Storage;

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])->except(['index', 'show', 'articles', 'followers']);
        $this->authorizeResource(Tag::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tags = Tag::orderBy('created_at', 'desc')->withCount('articles')->paginate(10);
        return TagOnlyResource::collection($tags);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateTag $request)
    {
        $data = $request->all();
        $data['slug'] = str_slug($data['name']) . '-' . base_convert(time(), 10, 36);
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/images/tag');
            $path = Storage::url($path);
            $data['image'] = $path;
        }
        $newTag = Tag::create($data);
        return new TagResource($newTag);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function show(Tag $tag)
    {
        return new TagResource($tag);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTag $request, Tag $tag)
    {
        $data = $request->all();
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/images/tag');
            $path = Storage::url($path);
            $data['image'] = $path;
        }
        $tag->update($data);
        return new TagResource($tag);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();
        return response()->json(null, 204);
    }

    public function articles(Tag $tag) {
        $articles = $tag->articles()->paginate(10);
        return ArticleResource::collection($articles);
    }

    public function follow(Request $request, Tag $tag) {

        $user = $request->user();
        if ($user->followingTags()->find($tag->id)) {
            return response()->json([
                'errors' => [
                    'message' => 'Already follow this tag'
                ]
            ], 422);
        }
        $user->followingTags()->attach($tag);
        return new UserWithFollowingTagsResource($user);
    }

    public function unfollow(Request $request, Tag $tag) {
        $user = $request->user();
        if (!$user->followingTags()->find($tag->id)) {
            return response()->json([
                'errors' => [
                    'message' => 'Has not followed this tag yet'
                ]
            ], 422);
        }
        $user->followingTags()->detach($tag);
        return new UserWithFollowingTagsResource($user);
    }

    public function followers(Tag $tag) {
        return UserResource::collection($tag->followers);
    }
}
