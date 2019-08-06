<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use UsesUuid;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'owner', 'description', 'image_id', 'active', 'slug',
    ];

    public $append = ['logo_url'];

    public function getLogoUrlAttribute()
    {
        return $this->image ? $this->image->url : 'https://i.imgur.com/DoPMECx.jpg';
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

    public function user()
    {
        return $this->belongsTo(User::class, 'owner', 'id');
    }

    public function image()
    {
        return $this->hasOne(Image::class, 'id', 'image_id');
    }
    public function memberRequests()
    {
        return $this->hasMany(InviteRequest::class)->whereRaw("invite_requests.status = 'pending' or invite_requests.status = 'accepted'");
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'invite_requests')
            ->withPivot(['status'])
            ->wherePivot('status', '<>', 'denied');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow_organizations');
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
