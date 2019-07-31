<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = [
        'username', 'email', 'reference_link', 'content'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function article() {
        return $this->belongsTo('App\Models\Article', 'article_id');
    }
}
