<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Series;
use Illuminate\Auth\Access\HandlesAuthorization;

class SeriesPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any series.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(?User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the series.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Series  $series
     * @return mixed
     */
    public function view(?User $user, Series $series)
    {
        return true;
    }

    /**
     * Determine whether the user can create series.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the series.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Series  $series
     * @return mixed
     */
    public function update(User $user, Series $series)
    {
        return $user->isAdmin() || ($user->id === $series->user_id);
    }

    /**
     * Determine whether the user can delete the series.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Series  $series
     * @return mixed
     */
    public function delete(User $user, Series $series)
    {
        return $user->isAdmin() || ($user->id === $series->user_id);
    }

    /**
     * Determine whether the user can restore the series.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Series  $series
     * @return mixed
     */
    public function restore(User $user, Series $series)
    {
        return $user->isAdmin() || ($user->id === $series->user_id);
    }

    /**
     * Determine whether the user can permanently delete the series.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Series  $series
     * @return mixed
     */
    public function forceDelete(User $user, Series $series)
    {
        return $user->isAdmin() || ($user->id === $series->user_id);
    }
}
