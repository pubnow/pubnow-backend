<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\User\ChangePassword;
use App\Http\Requests\Api\User\UpdateUser;
use App\Http\Requests\Api\User\CreateUser;
use App\Http\Resources\ArticleOnlyResource;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\BookmarkResource;
use App\Http\Resources\CategoryOnlyResource;
use App\Http\Resources\InviteRequestResource;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\SeriesResource;
use App\Http\Resources\TagOnlyResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserWithFollowingUsersResource;
use App\Http\Resources\UserWithFollowingCategoriesResource;
use App\Http\Resources\UserWithFollowingTagsResource;
use App\Models\Category;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\UserFollow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])
            ->except(['index', 'show', 'articles', 'followers', 'followingUsers', 'followingOrganizations', 'followingTags', 'followingCategories']);
        $this->authorizeResource(User::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return UserResource::collection(User::all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\Api\User\CreateUser  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateUser $request)
    {
        $data = $request->all();
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('public/images/avatar');
            $path = Storage::url($path);
            $data['avatar'] = $path;
        }
        $user = User::create($data);
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUser $request, User $user)
    {
        if ($request->has('email') || $request->has('username')) {
            return response()->json([
                'errors' => [
                    'message' => 'cannot update username or email',
                ]
            ], 403);
        }
        $data = $request->all();
        if ($request->has('role_id')) {
            if (!$request->user()->isAdmin()) {
                return response()->json([
                    'errors' => [
                        'message' => 'User cannot update own role',
                    ]
                ], 403);
            }
        }
        if ($request->has('password') && !$request->user()->isAdmin()) {
            return response()->json([
                'errors' => [
                    'message' => 'user cannot update password',
                ]
            ], 403);
        }
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('public/images/avatar');
            $path = Storage::url($path);
            $data['avatar'] = $path;
        }
        $user->update($data);
        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }

    public function changePassword(ChangePassword $request)
    {
        $user = $request->user();
        $oldPassword = $request->get('old_password');
        if (Hash::check($oldPassword, $user->password)) {
            $user->update([
                'password' => $request->get('new_password'),
            ]);
            return new UserResource($user);
        }
        return response()->json([
            'errors' => [
                'message' => 'Password is incorrect'
            ]
        ], 422);
    }

    public function articles(Request $request, User $user)
    {
        $articles = $user->articles()->withAuthor()->paginate(10);
        return ArticleOnlyResource::collection($articles);
    }

    public function bookmarks(Request $request)
    {
        $bookmark = $request->user()->bookmarks()->paginate(10);
        return BookmarkResource::collection($bookmark);
    }

    public function inviteRequests(Request $request)
    {
        $user = $request->user();
        return InviteRequestResource::collection($user->inviteRequests);
    }

    public function organizations(Request $request)
    {
        $user = $request->user();
        return OrganizationResource::collection($user->organizations);
    }

    public function follow(Request $request, User $user)
    {
        $follower = $request->user();
        if ($follower->followingUsers()->find($user->id)) {
            return response()->json([
                'errors' => [
                    'message' => 'Already followed this user',
                ]
            ], 422);
        }
        $follower->followingUsers()->attach($user);
        Notification::send($user, new UserFollow($follower));
        return new UserWithFollowingUsersResource($follower);
    }

    public function unfollow(Request $request, User $user)
    {
        $follower = $request->user();
        if (!$follower->followingUsers()->find($user->id)) {
            return response()->json([
                'errors' => [
                    'message' => 'Has not followed this user yet',
                ]
            ], 422);
        }
        $follower->followingUsers()->detach($user);
        return new UserWithFollowingUsersResource($follower);
    }

    // Get users who followed this user
    public function followers(User $user)
    {
        return UserResource::collection($user->followers);
    }

    // Get users who be followed by this user
    public function followingUsers(User $user)
    {
        return UserResource::collection($user->followingUsers);
    }

    // Get organizations who be followed by this user
    public function followingOrganizations(User $user)
    {
        return OrganizationResource::collection($user->followingOrganizations);
    }

    public function followingTags(Request $request, User $user)
    {
        $tags = $user->followingTags;
        return TagOnlyResource::collection($tags);
    }

    public function followingCategories(Request $request, User $user)
    {
        $categories = $user->followingCategories;
        return CategoryOnlyResource::collection($categories);
    }

    public function series(Request $request)
    {
        $series = $request->user()->series()->paginate(10);
        return SeriesResource::collection($series);
    }

    public function allArticles(Request $request)
    {
        $articles = $request->user()->articles()->paginate(10);
        return ArticleOnlyResource::collection($articles);
    }

    public function adminMembers(Request $request)
    {
        $this->authorize('filterUsers', User::class);
        $role_admin = Role::where('name', 'admin')->first();
        $users = User::where('role_id', $role_admin->id)->get();
        return UserResource::collection($users);
    }

    public function newMembers()
    {
        $this->authorize('filterUsers', User::class);
        $users = User::where('created_at', '>', Carbon::now()->subDays(7))->get();
        return UserResource::collection($users);
    }

    public function featuredAuthors()
    {
        $this->authorize('filterUsers', User::class);
        $users = User::with('articles')->get()->sortByDesc(function ($user) {
            return $user->articles->count();
        })->take(5);
        return UserResource::collection($users);
    }

    public function activeMembers()
    {
        $this->authorize('filterUsers', User::class);
        $users = User::with('claps')->with('comments')->get()->sortByDesc(function ($user) {
            return $user->claps->sum('count') + $user->comments->count();
        })->take(5);
        return UserResource::collection($users);
    }
}
