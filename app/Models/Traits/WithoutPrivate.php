<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait WithoutPrivate
{
    public function scopeWithoutPrivate(Builder $query)
    {
        return $query->where('draft', false)
            ->where('private', false)
            ->where('organization_private', false);
    }
}
