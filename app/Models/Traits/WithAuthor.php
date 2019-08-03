<?php


namespace App\Models\Traits;


use Illuminate\Database\Eloquent\Builder;

trait WithAuthor
{
    public function scopeWithAuthor(Builder $query)
    {
        $user = auth()->user();
        if (!$user) {
            return $query->where('draft', false)->where('private', false);
        }
        $privateArticles = $user->articles()->where('private', true);
        return $query->where('draft', false)
            ->where('private', false)
            ->union($privateArticles);
    }
}