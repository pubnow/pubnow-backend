<?php

namespace App\Policies;

use App\Models\User;
use App\Comment;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any comments.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can view the comment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Comment  $comment
     * @return mixed
     */
    public function view(User $user, Comment $comment)
    {
        return false;
    }

    /**
     * Determine whether the user can create comments.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the comment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Comment  $comment
     * @return mixed
     */
    public function update(User $user, Comment $comment)
    {
        return $user->id === $comment->user_id;
    }

    /**
     * Determine whether the user can delete the comment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Comment  $comment
     * @return mixed
     */
    public function delete(User $user, Comment $comment)
    {
        return $user->isAdmin() || ($user->id === $comment->user_id);
    }

    /**
     * Determine whether the user can restore the comment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Comment  $comment
     * @return mixed
     */
    public function restore(User $user, Comment $comment)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the comment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Comment  $comment
     * @return mixed
     */
    public function forceDelete(User $user, Comment $comment)
    {
        return $user->isAdmin() || ($user->id === $comment->user_id);
    }
}
