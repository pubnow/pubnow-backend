<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\User\UserClap;
use App\Http\Requests\Api\User\UpdateUser;
use App\Http\Resources\ClapResource;
use App\Http\Requests\Api\User\CreateUser;
use App\Http\Resources\UserResource;
use App\Models\Clap;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])->except(['index', 'show']);
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
    public function store(CreateUser $request) {
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

    public function clap(UserClap $request) {
        $user = $request->user();
        $clap = Clap::firstOrNew([
            'user_id' => $user->id,
            'article_id' => $request->get('article_id'),
        ]);
//        dd($clap->count);
        if ($clap->count !== null) {
            $clap->update([
                'count' => $clap->count + 1,
            ]);
        } else {
            $clap->count = 0;
            $clap->save();
        }
        return new ClapResource($clap);
    }

}
