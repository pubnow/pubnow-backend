<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Organization\CreateOrganization;
use App\Http\Requests\Api\Organization\UpdateOrganization;
use App\Http\Resources\InviteRequestResource;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])->except(['index', 'show', 'members']);
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

    public function members(Request $request, Organization $organization) {
        return InviteRequestResource::collection($organization->members);
    }
}
