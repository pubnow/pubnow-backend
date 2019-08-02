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
    public function store(CreateFeedback $request)
    {
        $user = auth()->user();
        $data = $request->only('reference', 'content', 'username', 'email');
        // nếu mà chưa đăng nhập, thì bắt nó nhập tên và email
        if (!$user) {
            if ((!array_key_exists("username", $data) || !array_key_exists("email", $data))) {
                return response()->json([
                    "errors" => [
                        "Username and email field must be filled."
                    ]
                ], 500);
            } else {
                return new FeedbackResource(Feedback::firstOrNew([
                    'article_id' => $request->id,
                    'reference' => $data['reference'],
                    'content' => $data['content'],
                    'username' => $data['username'],
                    'email' => $data['email']
                ]));
            }
        }
        // còn đã đăng nhập rồi thì username và email lấy lun
        $isExit = Feedback::where(['user_id' => $user->id, 'article_id' => $request->id])->first();
        if (!$isExit && $user) {
            $feedback = $user->feedback()->create([
                'article_id' => $request->id,
                'reference' => $data['reference'],
                'content' => $data['content'],
                'username' => $user->name,
                'email' => $user->email
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
