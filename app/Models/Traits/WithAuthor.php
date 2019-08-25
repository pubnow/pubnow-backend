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
        $userPrivateArticles = $query->where('user_id', $user->id)
            ->where('organization_id', null)
            ->where('private', true);
        $organizations = $user->organizations;
        $organizations->each(function ($organization) use ($userPrivateArticles) {
            $userPrivateArticles->union($organization->articles()->where('organization_private', true));
        });
        return $query->where('draft', false)
            ->where('private', false)
            ->where('organization_private', false)
            ->union($userPrivateArticles);
    }
}
