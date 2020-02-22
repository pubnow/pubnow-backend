<?php

namespace App\Policies;

use App\Models\InviteRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InviteRequestPolicy
{
    use HandlesAuthorization;
    public function index(User $user) {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view any join group requests.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }
    /**
     * Determine whether the user can view the join group request.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\InviteRequest  $joinGroupRequest
     * @return mixed
     */
    public function view(User $user, InviteRequest $joinGroupRequest)
    {
        return false;
    }
    /**
     * Determine whether the user can create join group requests.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }
    /**
     * Determine whether the user can delete the join group request.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\InviteRequest  $inviteRequest
     * @return mixed
     */
    public function delete(User $user, InviteRequest $inviteRequest)
    {
        return $user->id === $inviteRequest->organization->owner;
    }

    public function reply(User $user, InviteRequest $inviteRequest) {
        return $user->id === $inviteRequest->user_id;
    }
}
