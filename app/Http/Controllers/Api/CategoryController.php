<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Category\UpdateCategory;
use App\Http\Resources\ArticleOnlyResource;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\CategoryOnlyResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserWithFollowingCategoriesResource;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\Api\Category\CreateCategory;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])->except(['index', 'show', 'articles', 'followers']);
        $this->authorizeResource(Category::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();
        return CategoryOnlyResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\Api\Category\CreateCategory  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateCategory $request)
    {
        $data = $request->all();
        $data['slug'] = str_slug($data['name']) . '-' . base_convert(time(), 10, 36);
        $newCategory = Category::create($data);
        return new CategoryResource($newCategory);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCategory $request, Category $category)
    {
        $data = $request->only(['name', 'description', 'image_id']);
        if ($request->has('name') && !empty($data['name'])) {
            $data['slug'] = str_slug($data['name']) . '-' . base_convert(time(), 10, 36);
        }
        $category->update($data);
        return new CategoryResource($category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }

    public function articles(Category $category)
    {
        $articles = $category->articles()->withAuthor()->paginate(10);
        return ArticleOnlyResource::collection($articles);
    }

    public function follow(Request $request, Category $category)
    {
        $user = $request->user();
        if ($user->followingCategories()->find($category->id)) {
            return response()->json([
                'errors' => [
                    'message' => 'Already follow this category'
                ]
            ], 422);
        }
        $user->followingCategories()->attach($category);
        return new UserWithFollowingCategoriesResource($user);
    }

    public function unfollow(Request $request, Category $category)
    {
        $user = $request->user();
        if (!$user->followingCategories()->find($category->id)) {
            return response()->json([
                'errors' => [
                    'message' => 'Has not followed this category yet'
                ]
            ], 422);
        }
        $user->followingCategories()->detach($category);
        return new UserWithFollowingCategoriesResource($user);
    }

    public function followers(Category $category)
    {
        return UserResource::collection($category->followers);
    }
}
