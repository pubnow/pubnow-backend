<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Organization\CreateOrganization;
use App\Http\Requests\Api\Organization\FollowOrganization;
use App\Http\Requests\Api\Organization\UpdateOrganization;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\UserWithFollowedOrganizationsResource;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])->except(['index', 'show', 'getFollowers']);
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

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('public/images/avatar');
            $path = Storage::url($path);
            $data['avatar'] = $path;
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('public/images/logo');
            $path = Storage::url($path);
            $data['logo'] = $path;
        }

        $data['active'] = 0;
        $organization = Organization::create($data);
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
        $data = $request->all();

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('public/images/logo');
            $path = Storage::url($path);
            $data['logo'] = $path;
        }
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

    public function active(Request $request, Organization $organization) {
        $this->authorize('active', $organization);
        $organization->update([
            'active' => 1,
        ]);
        return new OrganizationResource($organization);
    }

    // Get users who followed this user
    public function getFollowers(Organization $organization) {
        return UserResource::collection($organization->followers);
    }

    public function follow(FollowOrganization $request) {
        $organization_id = $request->get('organization_id');
        $user = $request->user();
        if ($user->followingOrganizations()->find($organization_id)) {
            return response()->json([
                'errors' => [
                    'message' => 'Already followed this organization',
                ]
            ], 422);
        }
        $organization = Organization::find($organization_id);
        $user->followingOrganizations()->attach($organization);
        return new UserWithFollowedOrganizationsResource($user);
    }

    public function unfollow(FollowOrganization $request) {
        $organization_id = $request->get('organization_id');
        $user = $request->user();
        if (!$user->followingOrganizations()->find($organization_id)) {
            return response()->json([
                'errors' => [
                    'message' => 'Has not followed this organization yet',
                ]
            ], 422);
        }
        $organization = Organization::find($organization_id);
        $user->followingOrganizations()->detach($organization);
        return new UserWithFollowedOrganizationsResource($user);
    }
}
