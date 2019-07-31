<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\UsesUuid;
use Laravel\Scout\Searchable;

class Article extends Model
{
    use UsesUuid;
    use Searchable;

    public static function boot() {
        parent::boot();

        static::deleting(function($article) { // before delete() method call this
            $article->tags()->detach();
            $article->comments()->delete();
            $article->tags()->delete();
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'content', 'slug', 'user_id',
        'category_id', 'seen_count', 'excerpt', 'thumbnail', 'draft', 'private'
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();

        return array('title' => $array['title'], 'content' => $array['content']);
    }

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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function claps()
    {
        return $this->hasMany(Clap::class);
    }


    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }
}
