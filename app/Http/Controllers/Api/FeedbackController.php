<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Feedback\CreateFeedback;

class FeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])->except(['store']);
        $this->authorizeResource(Feedback::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('index', Feedback::class);
        $feedback = Feedback::latest()->paginate(10);
        return FeedbackResource::collection($feedback);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateFeedback $request)
    {
        $user = auth()->user();
        $data = $request->only('reference', 'content', 'username', 'email', 'article_id', 'type');
        if ($user) {
            $data['user_id'] = $user->id;
        }
        $feedback = Feedback::create($data);
        return new FeedbackResource($feedback);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function show(Feedback $feedback)
    {
        return new FeedbackResource($feedback);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Feedback $feedback)
    {
        $data = $request->only('reference', 'content');
        $feedback->update($data);
        return new FeedbackResource($feedback);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function destroy(Feedback $feedback)
    {
        $feedback->delete();
        return response()->json(null, 204);
    }
}
