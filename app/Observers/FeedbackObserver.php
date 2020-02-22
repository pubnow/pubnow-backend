<?php

namespace App\Observers;

use App\Models\Feedback;
use App\Models\User;
use App\Notifications\UserFeedback;
use Illuminate\Support\Facades\Notification;

class FeedbackObserver
{
    /**
     * Handle the feedback "created" event.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return void
     */
    public function created(Feedback $feedback)
    {
        $admins = User::all()->filter(function ($user) {
            return $user->isAdmin();
        });

        Notification::send($admins, new UserFeedback($feedback));
    }

    /**
     * Handle the feedback "updated" event.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return void
     */
    public function updated(Feedback $feedback)
    {
        //
    }

    /**
     * Handle the feedback "deleted" event.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return void
     */
    public function deleted(Feedback $feedback)
    {
        //
    }

    /**
     * Handle the feedback "restored" event.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return void
     */
    public function restored(Feedback $feedback)
    {
        //
    }

    /**
     * Handle the feedback "force deleted" event.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return void
     */
    public function forceDeleted(Feedback $feedback)
    {
        //
    }
}
