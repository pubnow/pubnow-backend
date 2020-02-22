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
        if (!$organization->active) {
            return response()->json([
                'errors' => [
                    'message' => 'Organization not activated',
                ]
            ], 422);
        }
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

    public function accept(Request $request, InviteRequest $inviteRequest)
    {
        $this->authorize('reply', $inviteRequest);

        if ($inviteRequest->status === 'pending') {
            $inviteRequest->update([
                'status' => 'accepted',
            ]);
            return new InviteRequestResource($inviteRequest);
        } else {
            return response()->json([
                'Errors' => [
                    'message' => 'Invite requests has been replied'
                ]
            ], 422);
        }
    }

    public function deny(Request $request, InviteRequest $inviteRequest)
    {
        $this->authorize('reply', $inviteRequest);

        if ($inviteRequest->status === 'pending') {
            $inviteRequest->update([
                'status' => 'denied',
            ]);
            return new InviteRequestResource($inviteRequest);
        } else {
            return response()->json([
                'Errors' => [
                    'message' => 'Invite requests has been replied'
                ]
            ], 422);
        }
    }
}
