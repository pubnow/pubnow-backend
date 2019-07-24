<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Category\UpdateCategory;
use App\Http\Resources\ArticleResource;
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
        $this->middleware(['auth'])->except(['index', 'show']);
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
        return CategoryResource::collection($categories);
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
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/images/tag');
            $path = Storage::url($path);
            $data['image'] = $path;
        }
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
        $data = $request->all();
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/images/tag');
            $path = Storage::url($path);
            $data['image'] = $path;
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

    public function articles(Category $category) {
        $articles = $category->articles()->paginate(10);
        return ArticleResource::collection($articles);
    }
}
