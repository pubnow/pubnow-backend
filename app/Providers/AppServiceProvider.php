<?php

namespace App\Providers;

use App\Models\Clap;
use App\Models\Comment;
use App\Models\Feedback;
use App\Models\Organization;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Observers\AccountObserver;
use App\Observers\ClapObserver;
use App\Observers\CommentObserver;
use App\Observers\FeedbackObserver;
use App\Observers\OrganizationObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(AccountObserver::class);
        Feedback::observe(FeedbackObserver::class);
        Organization::observe(OrganizationObserver::class);
        Clap::observe(ClapObserver::class);
        Comment::observe(CommentObserver::class);
    }
}
