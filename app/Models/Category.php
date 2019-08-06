<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\UsesUuid;

class Category extends Model
{
    use UsesUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'description', 'image_id',
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

    public function articles()
    {
        return $this->hasMany('App\Models\Article', 'category_id', 'id')->withAuthor();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow_categories');
    }

    public function photo()
    {
        return $this->hasOne(Image::class, 'id', 'image_id');
    }
}
