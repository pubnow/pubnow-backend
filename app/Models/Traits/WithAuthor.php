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
        return $query->where('draft', false)
            ->where('private', false)
            ->where('organization_private', false)
            ->union($query->where('user_id', $user->id)->where('private', true));
    }
}
