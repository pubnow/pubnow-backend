<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Bookmark\CreateBookmark;
use App\Http\Resources\BookmarkResource;
use App\Models\Bookmark;
use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use vendor\project\StatusTest;

class BookmarkController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function store(CreateBookmark $request)
    {
        $user = auth()->user();
        // check xem record này đã được tạo chưa
        $isExit = Bookmark::where(['user_id' => $user->id, 'article_id' => $request->id])->first();
        if (!$isExit && $user) {
            $bookmark = $user->bookmarks()->create([
                'article_id' => $request->id,
            ]);
            return new BookmarkResource($bookmark);
        }
    }

    public function destroy(CreateBookmark $request)
    {
        $userId =$request->user()->id;
        $articleId = $request->id;
        $bookmark = Bookmark::where(['user_id' => $userId, 'article_id' => $articleId]);
        dd($bookmark);
        $bookmark->delete();
        return response()->json(null, 204);
    }
}
