<?php

namespace App\Observers;

use App\Models\Clap;
use App\Notifications\ClapArticle;
use Illuminate\Support\Facades\Notification;

class ClapObserver
{
    /**
     * Handle the clap "created" event.
     *
     * @param  \App\Models\Clap  $clap
     * @return void
     */
    public function created(Clap $clap)
    {
        $author = $clap->article->author;
        $user = $clap->user;
        if ($author->id != $user->id) {
            Notification::send($user, new ClapArticle($clap));
        }
    }

    /**
     * Handle the clap "updated" event.
     *
     * @param  \App\Models\Clap  $clap
     * @return void
     */
    public function updated(Clap $clap)
    {
        //
    }

    /**
     * Handle the clap "deleted" event.
     *
     * @param  \App\Models\Clap  $clap
     * @return void
     */
    public function deleted(Clap $clap)
    {
        //
    }

    /**
     * Handle the clap "restored" event.
     *
     * @param  \App\Models\Clap  $clap
     * @return void
     */
    public function restored(Clap $clap)
    {
        //
    }

    /**
     * Handle the clap "force deleted" event.
     *
     * @param  \App\Models\Clap  $clap
     * @return void
     */
    public function forceDeleted(Clap $clap)
    {
        //
    }
}
