<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use UsesUuid;

    protected $fillable = [
        'username', 'email', 'reference', 'title', 'content', 'article_id', 'type', 'user_id', 'resolve'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function article()
    {
        return $this->belongsTo('App\Models\Article', 'article_id');
    }
}
