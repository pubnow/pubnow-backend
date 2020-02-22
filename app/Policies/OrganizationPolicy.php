<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any organizations.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(?User $user)
    {
        return true;
    }
    /**
     * Determine whether the user can view the organization.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization  $organization
     * @return mixed
     */
    public function view(?User $user, Organization $organization)
    {
        return true;
    }
    /**
     * Determine whether the user can create organizations.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }
    /**
     * Determine whether the user can update the organization.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization  $organization
     * @return mixed
     */
    public function update(User $user, Organization $organization)
    {
        return $user->isAdmin() || ($user->id === $organization->owner);
    }
    /**
     * Determine whether the user can delete the organization.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization  $organization
     * @return mixed
     */
    public function delete(User $user, Organization $organization)
    {
        return $user->isAdmin() || ($user->id === $organization->owner);
    }

    public function statistic(User $user, Organization $organization) {
        return $user->id === $organization->owner;
    }
}
