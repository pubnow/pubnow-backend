<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InviteRequest\CreateInviteRequest;
use App\Http\Resources\InviteRequestResource;
use App\Models\InviteRequest;
use App\Models\Organization;
use Illuminate\Http\Request;

class InviteRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->authorizeResource(InviteRequest::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(InviteRequest $inviteRequest)
    {
        $this->authorize('index', $inviteRequest);
        $requests = InviteRequest::all();
        return InviteRequestResource::collection($requests);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateInviteRequest $request)
    {
        $data = $request->all();
        $organization = Organization::find($request->input('organization_id'));
        if ($request->user()->id !== $organization->owner) {
            return response()->json([
                'errors' => [
                    'message' => 'Not organization owner'
                ]
            ], 422);
        }
        $inviteRequest = InviteRequest::firstOrNew($data);
        if ($inviteRequest->status === 'accepted' || $inviteRequest->status === 'pending') {
            return response()->json([
                'errors' => [
                    'message' => 'Invite request exists'
                ]
            ], 422);
        } elseif ($inviteRequest->status === 'denied') {
            $inviteRequest->update([
                'status' => 'pending',
            ]);
        } else {
            $inviteRequest->status = 'pending';
            $inviteRequest->save();
        }
        return new InviteRequestResource($inviteRequest);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InviteRequest  $inviteRequest
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InviteRequest $inviteRequest)
    {
        if ($inviteRequest->status === 'pending') {
            $status = $request->input('status');
            if ($status === 'accepted' || $status === 'denied') {
                $inviteRequest->update([
                    'status' => $request->input('status'),
                ]);
                return new InviteRequestResource($inviteRequest);
            } else {
                return response()->json([
                    'Errors' => [
                        'message' => 'Status must be accepted or denied'
                    ]
                ], 422);
            }
        } else {
            return response()->json([
                'Errors' => [
                    'message' => 'Invite requests has been replied'
                ]
            ], 422);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function destroy(InviteRequest $inviteRequest)
    {
        $inviteRequest->delete();
        return response()->json(null, 204);
    }
}
