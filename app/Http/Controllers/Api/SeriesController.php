<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Series\CreateSeries;
use App\Http\Resources\SeriesResource;
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
        $series = Series::orderByDesc('created_at')->paginate(10);
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

        $slug = str_slug($data['title']) . '-' . base_convert(time(), 10, 36);
        $series->update([
            'title' => $data['title'],
            'content' => $data['content'],
            'slug' => $slug
        ]);

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
}
