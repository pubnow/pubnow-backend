<?php

namespace App\Policies;

use App\Models\User;
use App\Models\InviteRequest;
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
        return $user->isAdmin() || ($user->id === $joinGroupRequest->user_id);
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
     * Determine whether the user can update the join group request.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\InviteRequest  $joinGroupRequest
     * @return mixed
     */
    public function update(User $user, InviteRequest $joinGroupRequest)
    {
        return $user->id === $joinGroupRequest->user_id;
    }

    /**
     * Determine whether the user can delete the join group request.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\InviteRequest  $joinGroupRequest
     * @return mixed
     */
    public function delete(User $user, InviteRequest $joinGroupRequest)
    {
        return $user->id === $joinGroupRequest->user_id;
    }

    /**
     * Determine whether the user can restore the join group request.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\InviteRequest  $joinGroupRequest
     * @return mixed
     */
    public function restore(User $user, InviteRequest $joinGroupRequest)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the join group request.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\InviteRequest  $joinGroupRequest
     * @return mixed
     */
    public function forceDelete(User $user, InviteRequest $joinGroupRequest)
    {
        return $user->id === $joinGroupRequest->user_id;
    }
}
