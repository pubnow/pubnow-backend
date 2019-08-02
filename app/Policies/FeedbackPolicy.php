<?php

namespace App\Policies;

use App\Models\User;
use App\Feedback;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeedbackPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any feedback.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the feedback.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Feedback  $feedback
     * @return mixed
     */
    public function view(User $user, Feedback $feedback)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create feedback.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(?User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the feedback.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Feedback  $feedback
     * @return mixed
     */
    public function update(User $user, Feedback $feedback)
    {
        return $user->isAdmin() || ($user->id === $feedback->user_id);
    }

    /**
     * Determine whether the user can delete the feedback.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Feedback  $feedback
     * @return mixed
     */
    public function delete(User $user, Feedback $feedback)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the feedback.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Feedback  $feedback
     * @return mixed
     */
    public function forceDelete(User $user, Feedback $feedback)
    {
        return $user->isAdmin();
    }
}
