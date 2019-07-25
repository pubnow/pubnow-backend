<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\User\UpdateUser;
use App\Http\Requests\Api\User\CreateUser;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserWithFollowingCategoriesResource;
use App\Http\Resources\UserWithFollowingTagsResource;
use App\Models\Category;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])->except(['index', 'show', 'articles']);
        $this->authorizeResource(User::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return UserResource::collection(User::all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\Api\User\CreateUser  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateUser $request)
    {
        $data = $request->all();
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('public/images/avatar');
            $path = Storage::url($path);
            $data['avatar'] = $path;
        }
        $role = Role::where(['name' => 'member'])->first();
        $data['role_id'] = $role->id;
        $user = User::create($data);
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        if ($request->has('email') || $request->has('username')) {
            return response()->json([
                'errors' => [
                    'message' => 'cannot update username or email',
                ]
            ], 403);
        }
        $data = $request->all();
        if ($request->has('role_id') && !$request->user()->isAdmin()) {
            return response()->json([
                'errors' => [
                    'message' => 'user cannot update own role',
                ]
            ], 403);
        }
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('public/images/avatar');
            $path = Storage::url($path);
            $data['avatar'] = $path;
        }
        $user->update($data);
        return new UserResource($user);
    }

    public function destroy(User $user) {
        $user->delete();
        return response()->json(null, 204);
    }

    public function articles(Request $request, User $user) {
        $articles = $user->articles()->paginate(10);
        return ArticleResource::collection($articles);
    }

    public function followTag(Request $request, Tag $tag) {
        $user = $request->user();
        if ($user->followingTags()->exists($tag)) {
            return response()->json([
                'errors' => [
                    'message' => 'Already follow this tag'
                ]
            ]);
        }
        $user->followingTags()->attach($tag);
        return new UserWithFollowingTagsResource($user);
    }

    public function unfollowTag(Request $request, Tag $tag) {
        $user = $request->user();
        if (!$user->followingTags()->exists($tag)) {
            return response()->json([
                'errors' => [
                    'message' => 'Has not followed this tag yet'
                ]
            ]);
        }
        $user->followingTags()->detach($tag);
        return new UserWithFollowingTagsResource($user);
    }

    public function followCategory(Request $request, Category $category) {
        $user = $request->user();
        if ($user->followingCategories()->exists($category)) {
            return response()->json([
                'errors' => [
                    'message' => 'Already follow this category'
                ]
            ]);
        }
        $user->followingCategories()->attach($category);
        return new UserWithFollowingCategoriesResource($user);
    }

    public function unfollowCategory(Request $request, Category $category) {
        $user = $request->user();
        if (!$user->followingCategories()->exists($category)) {
            return response()->json([
                'errors' => [
                    'message' => 'Has not followed this category yet'
                ]
            ]);
        }
        $user->followingCategories()->detach($category);
        return new UserWithFollowingCategoriesResource($user);
    }

}
