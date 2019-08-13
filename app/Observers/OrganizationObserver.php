<?php

namespace App\Observers;

use App\Models\Organization;
use App\Models\User;
use App\Notifications\OrganizationCreate;
use Illuminate\Support\Facades\Notification;

class OrganizationObserver
{
    /**
     * Handle the organization "created" event.
     *
     * @param  \App\Models\Organization  $organization
     * @return void
     */
    public function created(Organization $organization)
    {
        $admins = User::all()->filter(function ($user) {
            return $user->isAdmin();
        });

        Notification::send($admins, new OrganizationCreate($organization));
    }

    /**
     * Handle the organization "updated" event.
     *
     * @param  \App\Models\Organization  $organization
     * @return void
     */
    public function updated(Organization $organization)
    {
        //
    }

    /**
     * Handle the organization "deleted" event.
     *
     * @param  \App\Models\Organization  $organization
     * @return void
     */
    public function deleted(Organization $organization)
    {
        //
    }

    /**
     * Handle the organization "restored" event.
     *
     * @param  \App\Models\Organization  $organization
     * @return void
     */
    public function restored(Organization $organization)
    {
        //
    }

    /**
     * Handle the organization "force deleted" event.
     *
     * @param  \App\Models\Organization  $organization
     * @return void
     */
    public function forceDeleted(Organization $organization)
    {
        //
    }
}
