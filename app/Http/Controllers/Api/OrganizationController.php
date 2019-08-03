<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Organization\CreateOrganization;
use App\Http\Requests\Api\Organization\FollowOrganization;
use App\Http\Requests\Api\Organization\UpdateOrganization;
use App\Http\Resources\InviteRequestResource;
use App\Http\Resources\OrganizationMemberResource;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserWithFollowingOrganizationsResource;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])->except(['index', 'show', 'members', 'followers']);
        $this->authorizeResource(Organization::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return OrganizationResource::collection(Organization::all()->sortBy('created_at'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateOrganization $request)
    {
        $user = $request->user();
        $data = $request->except('active');
        $data['owner'] = $user->id;
        $data['active'] = 0;
        $organization = Organization::create($data);
        $organization->followers()->attach($user);
        $organization->members()->attach($user, [
            'id' => DB::raw('gen_random_uuid()'),
            'status' => 'accepted'
        ]);
        return new OrganizationResource($organization);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function show(Organization $organization)
    {
        return new OrganizationResource($organization);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOrganization $request, Organization $organization)
    {
        $user = $request->user();
        if ($request->has('active') && !$user->isAdmin()) {
            return response()->json([
                'errors' => [
                    'message' => 'Only admin can active organization',
                ]
            ], 403);
        }
        $data = $request->all();
        $organization->update($data);
        return new OrganizationResource($organization);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function destroy(Organization $organization)
    {
        $organization->delete();
        return response()->json(null, 204);
    }

    public function members(Request $request, Organization $organization) {
        return OrganizationMemberResource::collection($organization->members);
    }

    // Get users who followed this user
    public function followers(Organization $organization) {
        return UserResource::collection($organization->followers);
    }

    public function follow(Request $request, Organization $organization) {
        $user = $request->user();
        if ($user->followingOrganizations()->find($organization->id)) {
            return response()->json([
                'errors' => [
                    'message' => 'Already followed this organization',
                ]
            ], 422);
        }
        $user->followingOrganizations()->attach($organization);
        return new UserWithFollowingOrganizationsResource($user);
    }

    public function unfollow(Request $request, Organization $organization) {
        $user = $request->user();
        if (!$user->followingOrganizations()->find($organization->id)) {
            return response()->json([
                'errors' => [
                    'message' => 'Has not followed this organization yet',
                ]
            ], 422);
        }
        $user->followingOrganizations()->detach($organization);
        return new UserWithFollowingOrganizationsResource($user);
    }
}
