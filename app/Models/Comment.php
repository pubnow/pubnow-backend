<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use UsesUuid;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content', 'article_id', 'parent_id', 'user_id', 'up_point', 'down_point'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function article() {
        return $this->belongsTo('App\Models\Article');
    }

    public function parent() {
        return $this->belongsTo('App\Models\Comment', 'parent', 'id');
    }

    public function childs() {
        return $this->hasMany('App\Models\Comment', 'parent', 'id');
    }
}
