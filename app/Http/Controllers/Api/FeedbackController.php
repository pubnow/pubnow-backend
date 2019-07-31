<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CreateFeedback;
use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])->except(['store', 'update']);
        $this->authorizeResource(Feedback::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return FeedbackResource::collection(Feedback::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $data = $request->only('reference', 'content');
        dd($data);
        // check xem record này đã được tạo chưa
        $isExit = Feedback::where(['user_id' => $user->id, 'article_id' => $request->id])->first();
        if (!$isExit && $user) {
            $feedback = $user->feedback()->create([
                'article_id' => $request->id,
                'reference' => $request->id,
                'content' => $request->id
            ]);
            return new FeedbackResource($feedback);
        }
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
        //
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
