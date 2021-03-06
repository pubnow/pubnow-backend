<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Role\CreateRole;
use App\Http\Requests\Api\Role\UpdateRole;
use App\Models\Role;
use App\Http\Resources\RoleResource;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(["auth"]);
        $this->authorizeResource(Role::class);
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

    public function store(CreateRole $request) {
        $role = Role::create($request->all());
        return new RoleResource($role);
    }

    public function update(UpdateRole $request, Role $role) {
        $role_name = $role->name;
        if ($role_name === 'admin' || $role_name === 'member') {
            return response()->json([
                'errors' => [
                    'message' => 'Cannot update role admin and member',
                ]
            ], 422);
        }

        $role->update($request->all());
        return new RoleResource($role);
    }

    public function destroy(Role $role) {
        $users = $role->users;
        foreach($users as $user) {
            $user->update([
                'role_id' => null,
            ]);
        }
        $role->delete();
        return response()->json(null, 204);
    }
}
