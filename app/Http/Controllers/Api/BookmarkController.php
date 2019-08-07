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

    public function store(CreateBookmark $request, Article $article)
    {
        $user = auth()->user();
        // check xem record này đã được tạo chưa
        $isExist = Bookmark::where(['user_id' => $user->id, 'article_id' => $article->id])->first();

        if (!$isExist) {
            $bookmark = $user->bookmarks()->create([
                'article_id' => $article->id,
            ]);
            return new BookmarkResource($bookmark);
        }
    }

    public function destroy(CreateBookmark $request, Article $article)
    {
        $user = auth()->user();
        // check xem record này đã được tạo chưa
        $bookmark = Bookmark::where(['user_id' => $user->id, 'article_id' => $article->id])->first();
        if (!$bookmark) {
            return response()->json(["errors" => "The bookmark does not exist."], 403);
        }
        if ($user->isAdmin() || ($user->id === $bookmark->user->id)) {
            $bookmark->delete();
            return response()->json(null, 204);
        }
    }
}
