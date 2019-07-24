<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\User\UpdateUser;
use App\Http\Requests\Api\User\CreateUser;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserWithFollowedOrganizationsResource;
use App\Http\Resources\UserWithFollowedUsersResource;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])
            ->except(['index', 'show', 'getFollowUsers', 'getFollowers', 'getOrganizationsFollowed', 'articles']);
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
    public function update(Request $request, User $user)
    {
        if ($request->has('email') || $request->has('username')) {
            return response()->json([
                'errors' => [
                    'message' => 'cannot update username or email',
                ]
            ], 403);
        }
        $data = $request->all();
        if ($request->has('role_id') && !$request->user()->isAdmin()) {
            return response()->json([
                'errors' => [
                    'message' => 'user cannot update own role',
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

    public function followOrganization(Request $request, Organization $organization) {
        $user = $request->user();
        $user->organizationsFollowed()->attach($organization);
        return new UserWithFollowedOrganizationsResource($user);
    }

    public function unfollowOrganization(Request $request, Organization $organization) {
        $user = $request->user();
        $user->organizationsFollowed()->detach($organization);
        return new UserWithFollowedOrganizationsResource($user);
    }

    public function followUser(Request $request, User $user) {
        $follower = $request->user();
        $follower->usersFollowed()->attach($user);
        return new UserWithFollowedUsersResource($follower);
    }

    public function unfollowUser(Request $request, User $user) {
        $follower = $request->user();
        $follower->usersFollowed()->detach($user);
        return new UserWithFollowedUsersResource($follower);
    }

    // Get users who followed this user
    public function getFollowers(User $user) {
        return UserResource::collection($user->followers);
    }

    // Get users who be followed by this user
    public function getUsersFollowed(User $user) {
        return UserResource::collection($user->usersFollowed);
    }

    // Get organizations who be followed by this user
    public function getOrganizationsFollowed(User $user) {
        return OrganizationResource::collection($user->organizationsFollowed);
    }

    public function articles(Request $request, User $user) {
        $articles = $user->articles()->paginate(10);
        return ArticleResource::collection($articles);
    }
}
