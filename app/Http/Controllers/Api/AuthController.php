<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginUser;
use App\Http\Requests\Api\Auth\RegisterUser;
use App\Http\Requests\Api\Auth\UpdateUser;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginUser $request)
    {
        $credentials = $request->only('username', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'errors' => [
                    'email or password' => 'is invalid',
                ]
            ], 422);
        }

        return (new UserResource(auth()->user()))->additional([
            'token' => $token,
        ]);
    }

    public function register(RegisterUser $request)
    {
        $user = User::create($request->all());

        $token = auth()->login($user);

        return (new UserResource(auth()->user()))->additional([
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return new UserResource($user);
    }
}
