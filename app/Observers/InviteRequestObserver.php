<?php

namespace App\Observers;

use App\Models\InviteRequest;
use App\Notifications\InviteUserToOrganization;
use Illuminate\Support\Facades\Notification;

class InviteRequestObserver
{
    /**
     * Handle the invite request "created" event.
     *
     * @param  \App\Models\InviteRequest  $inviteRequest
     * @return void
     */
    public function created(InviteRequest $inviteRequest)
    {
        $user = $inviteRequest->user;
        Notification::send($user, new InviteUserToOrganization($inviteRequest));
    }

    /**
     * Handle the invite request "updated" event.
     *
     * @param  \App\Models\InviteRequest  $inviteRequest
     * @return void
     */
    public function updated(InviteRequest $inviteRequest)
    {
        //
    }

    /**
     * Handle the invite request "deleted" event.
     *
     * @param  \App\Models\InviteRequest  $inviteRequest
     * @return void
     */
    public function deleted(InviteRequest $inviteRequest)
    {
        //
    }

    /**
     * Handle the invite request "restored" event.
     *
     * @param  \App\Models\InviteRequest  $inviteRequest
     * @return void
     */
    public function restored(InviteRequest $inviteRequest)
    {
        //
    }

    /**
     * Handle the invite request "force deleted" event.
     *
     * @param  \App\Models\InviteRequest  $inviteRequest
     * @return void
     */
    public function forceDeleted(InviteRequest $inviteRequest)
    {
        //
    }
}
