<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Comment\CreateComment;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommentController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateComment $request)
    {
        if ($request->has('parent_id') && !empty($request->get('parent_id'))) {
            $comment = Comment::find($request->get('parent_id'));
            if ($comment && $comment->parent()->exists() && $comment->parent->parent()->exists()) {
                return response()->json([
                    'errors' => [
                        'message' => 'Comments is limited at level 3'
                    ]
                ]);
            }
        }
        $user = $request->user();
        $data = $request->all();
        $data['user_id'] = $user->id;
        $comment = Comment::create($data);
        return new CommentResource($comment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Comment $comment)
    {
        if ($request->has('content')) {
            $comment->update([
                'content' => $request->get('content'),
            ]);
        }
        return new CommentResource($comment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();
        return response()->json(null, 204);
    }
}
