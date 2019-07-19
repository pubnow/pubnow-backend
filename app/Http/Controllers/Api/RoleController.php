<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Http\Resources\RoleResource;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(["auth"]);
    }
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'message' => 'This action is unauthorized.',
            ], 403);
        }
        $roles = Role::all();
        return RoleResource::collection($roles);
    }
}
