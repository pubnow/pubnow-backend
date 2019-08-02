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
        'name', 'email', 'owner', 'description', 'image_id', 'active',
    ];
    public function user() {
        return $this->belongsTo(User::class, 'owner', 'id');
    }

    public function image()
    {
        return $this->hasOne(Image::class, 'id', 'image_id');
    }

    public function memberRequests() {
        return $this->hasMany(InviteRequest::class)->whereRaw("invite_requests.status = 'pending' or invite_requests.status = 'accepted'");
    }

    public function members() {
        return $this->belongsToMany(User::class, 'invite_requests')
            ->whereRaw("invite_requests.status = 'pending' or invite_requests.status = 'accepted'")
            ->withPivot(['status']);
    }

    public function followers() {
        return $this->belongsToMany(User::class, 'user_follow_organizations');
    }
}
