<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Bookmark\CreateBookmark;
use App\Http\Resources\BookmarkResource;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $user_id = $request->user()->id;
        $bookmarks = Bookmark::where('user_id', $user_id)->get();
        return BookmarkResource::collection($bookmarks);
    }

    public function store(CreateBookmark $request)
    {
        $user = $request->user();
        $bookmark = $user->bookmarks()->create([
            'article_id' => $request->get('article_id'),
        ]);
        return new BookmarkResource($bookmark);
    }

    public function destroy(Bookmark $bookmark)
    {
        $bookmark->delete();
        return response()->json(null, 204);
    }
}
