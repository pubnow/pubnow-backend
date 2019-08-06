<?php


namespace App\Models\Traits;


use Illuminate\Database\Eloquent\Builder;

trait WithAuthor
{
    public function scopeWithAuthor(Builder $query)
    {
        $user = auth()->user();
        if (!$user) {
            return $query->where('draft', false)
                ->where('private', false)
                ->where('organization_private', false);
        }
        $userPrivateArticles = $user->articles()
            ->where('organization_id', null)
            ->where('private', true);
        $organizationPrivateArticles = $user->articles()
            ->where('organization_id', '<>', null)
            ->where('organization_private', true);
        return $query->where('draft', false)
            ->where('private', false)
            ->where('organization_private', false)
            ->union($userPrivateArticles)
            ->union($organizationPrivateArticles);
    }
}