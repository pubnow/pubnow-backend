<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    use UsesUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'content', 'slug', 'user_id',
    ];

    /**
     * Get the key name for route model binding.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }


    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function articles() {
        return $this->hasMany('App\Models\Article');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
